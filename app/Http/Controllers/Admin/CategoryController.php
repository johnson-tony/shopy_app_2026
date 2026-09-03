<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Models\Mode;
use App\Services\CloudinaryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CategoryController extends Controller
{
    protected CloudinaryService $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Display a listing of categories with search and filtering.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $parentFilter = $request->input('parent');
        $statusFilter = $request->input('status');
        $featuredFilter = $request->input('featured');
        $modeFilter = $request->input('mode');

        $query = Category::with('parent', 'children', 'mode');

        // Search Filter
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Mode Filter
        if ($modeFilter !== null && $modeFilter !== '') {
            $query->where('mode_id', (int) $modeFilter);
        }

        // Parent / Hierarchy Filter
        if ($parentFilter === 'root') {
            $query->whereNull('parent_id');
        } elseif ($parentFilter === 'sub') {
            $query->whereNotNull('parent_id');
        } elseif (is_numeric($parentFilter)) {
            $query->where('parent_id', (int) $parentFilter);
        }

        // Status Filter
        if ($statusFilter === 'active' || $statusFilter === '1') {
            $query->where('status', true);
        } elseif ($statusFilter === 'inactive' || $statusFilter === '0') {
            $query->where('status', false);
        }

        // Featured Filter
        if ($featuredFilter === 'yes' || $featuredFilter === '1') {
            $query->where('is_featured', true);
        } elseif ($featuredFilter === 'no' || $featuredFilter === '0') {
            $query->where('is_featured', false);
        }

        $categories = $query->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Statistics for Top Metrics Cards
        $stats = [
            'total' => Category::count(),
            'root' => Category::whereNull('parent_id')->count(),
            'sub' => Category::whereNotNull('parent_id')->count(),
            'active' => Category::where('status', true)->count(),
            'featured' => Category::where('is_featured', true)->count(),
        ];

        // List of all root categories for filter dropdown
        $rootCategories = Category::whereNull('parent_id')->orderBy('name', 'asc')->get();
        $modes = Mode::active()->ordered()->get();

        return view('admin.categories.index', compact(
            'categories',
            'stats',
            'search',
            'parentFilter',
            'statusFilter',
            'featuredFilter',
            'modeFilter',
            'rootCategories',
            'modes'
        ));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        $parentCategories = Category::whereNull('parent_id')->orderBy('name', 'asc')->get();
        $modes = Mode::active()->ordered()->get();

        return view('admin.categories.create', compact('parentCategories', 'modes'));
    }

    /**
     * Store a newly created category in database with Cloudinary image upload.
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Auto generate unique slug if not manually provided
        if (empty($data['slug'])) {
            $data['slug'] = Category::generateUniqueSlug($data['name']);
        }

        // Handle Cloudinary Image Upload
        if ($request->hasFile('image')) {
            try {
                $data['image'] = $this->cloudinaryService->uploadCategoryImage($request->file('image'));
            } catch (Exception $e) {
                Log::error('Category Image Upload Failed', ['error' => $e->getMessage()]);
                return back()
                    ->withErrors(['image' => 'Image upload to Cloudinary failed: ' . $e->getMessage()])
                    ->withInput();
            }
        }

        // Auto-inherit mode from parent category if mode is omitted
        if (empty($data['mode_id']) && !empty($data['parent_id'])) {
            $parent = Category::find($data['parent_id']);
            $data['mode_id'] = $parent?->mode_id;
        }

        // Default to Shopy mode if still empty
        if (empty($data['mode_id'])) {
            $data['mode_id'] = Mode::where('slug', 'shopy')->value('id');
        }

        $category = Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', "Category '{$category->name}' has been created successfully.");
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): View
    {
        // Exclude self and all descendants to prevent circular hierarchy
        $excludedIds = array_merge([$category->id], $category->getDescendantIds());
        $parentCategories = Category::whereNotIn('id', $excludedIds)
            ->whereNull('parent_id')
            ->orderBy('name', 'asc')
            ->get();
        $modes = Mode::active()->ordered()->get();

        return view('admin.categories.edit', compact('category', 'parentCategories', 'modes'));
    }

    /**
     * Update the specified category in database with Cloudinary image replacement.
     */
    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        // Auto-inherit mode from parent category if mode is omitted
        if (empty($data['mode_id']) && !empty($data['parent_id'])) {
            $parent = Category::find($data['parent_id']);
            $data['mode_id'] = $parent?->mode_id;
        }

        // Auto generate unique slug if empty
        if (empty($data['slug'])) {
            $data['slug'] = Category::generateUniqueSlug($data['name'], $category->id);
        }

        // Handle Image Upload / Replacement in Cloudinary
        if ($request->hasFile('image')) {
            try {
                $newImageUrl = $this->cloudinaryService->uploadCategoryImage($request->file('image'));

                // Delete old Cloudinary image if exists
                if ($category->image) {
                    $this->deleteCategoryImage($category->image);
                }

                $data['image'] = $newImageUrl;
            } catch (Exception $e) {
                Log::error('Category Image Replacement Failed', ['error' => $e->getMessage()]);
                return back()
                    ->withErrors(['image' => 'Image upload to Cloudinary failed: ' . $e->getMessage()])
                    ->withInput();
            }
        }

        // Handle Image Removal Checkbox
        if ($request->boolean('remove_image')) {
            if ($category->image) {
                $this->deleteCategoryImage($category->image);
            }
            $data['image'] = null;
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', "Category '{$category->name}' has been updated successfully.");
    }

    /**
     * Remove the specified category from database and delete its Cloudinary image.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $name = $category->name;

        // Delete associated image from Cloudinary or local storage
        if ($category->image) {
            $this->deleteCategoryImage($category->image);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', "Category '{$name}' has been deleted successfully.");
    }

    /**
     * Helper to safely remove category image from Cloudinary or local storage.
     */
    protected function deleteCategoryImage(string $imagePath): void
    {
        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://') || str_starts_with($imagePath, 'category')) {
            $this->cloudinaryService->deleteImage($imagePath);
        } elseif (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    /**
     * Toggle active status via 1-click switch.
     */
    public function toggleStatus(Category $category, Request $request): RedirectResponse|JsonResponse
    {
        $category->status = !$category->status;
        $category->save();

        $statusText = $category->status ? 'activated' : 'deactivated';
        $message = "Category '{$category->name}' has been {$statusText}.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $category->status,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Toggle featured status via 1-click badge switch.
     */
    public function toggleFeatured(Category $category, Request $request): RedirectResponse|JsonResponse
    {
        $category->is_featured = !$category->is_featured;
        $category->save();

        $featuredText = $category->is_featured ? 'marked as featured' : 'unmarked from featured';
        $message = "Category '{$category->name}' has been {$featuredText}.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_featured' => $category->is_featured,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}

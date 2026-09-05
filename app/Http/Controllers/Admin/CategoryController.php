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
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    protected CloudinaryService $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Display a listing of categories with search and filtering,
     * scoped to the admin's authorized shopping modes.
     */
    public function index(Request $request): View
    {
        $admin = auth('admin')->user();
        $search = $request->string('search')->trim()->toString();
        $parentFilter = $request->input('parent');
        $statusFilter = $request->input('status');
        $featuredFilter = $request->input('featured');
        $modeFilter = $request->input('mode');

        $query = Category::with('parent', 'children', 'mode');

        // Scoped to admin's allowed modes
        if ($admin && !$admin->isSuperAdmin()) {
            $allowedModeIds = $admin->getAllowedModeIds();
            $query->whereIn('mode_id', $allowedModeIds);
        }

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
            if ($admin && !$admin->hasModeAccess((int) $modeFilter)) {
                abort(403, 'Unauthorized. You do not have permission to view categories in this shopping mode.');
            }
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
        $statsQuery = Category::query();
        if ($admin && !$admin->isSuperAdmin()) {
            $statsQuery->whereIn('mode_id', $admin->getAllowedModeIds());
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'root' => (clone $statsQuery)->whereNull('parent_id')->count(),
            'sub' => (clone $statsQuery)->whereNotNull('parent_id')->count(),
            'active' => (clone $statsQuery)->where('status', true)->count(),
            'featured' => (clone $statsQuery)->where('is_featured', true)->count(),
        ];

        // List of all root categories for filter dropdown
        $rootCategories = Category::whereNull('parent_id')
            ->when($admin && !$admin->isSuperAdmin(), fn ($q) => $q->whereIn('mode_id', $admin->getAllowedModeIds()))
            ->orderBy('name', 'asc')
            ->get();
        $modes = $admin ? $admin->getAllowedModes() : Mode::active()->ordered()->get();

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
        $admin = auth('admin')->user();
        $modes = $admin ? $admin->getAllowedModes() : Mode::active()->ordered()->get();
        $rootCategories = Category::whereNull('parent_id')
            ->when($admin && !$admin->isSuperAdmin(), fn ($q) => $q->whereIn('mode_id', $admin->getAllowedModeIds()))
            ->orderBy('name', 'asc')
            ->get();
        $parentCategories = $rootCategories;

        return view('admin.categories.create', compact('rootCategories', 'parentCategories', 'modes'));
    }

    /**
     * Store a newly created category in database with mode access validation.
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        $admin = auth('admin')->user();
        $data = $request->validated();

        // Check Mode authorization
        if ($admin && !empty($data['mode_id']) && !$admin->hasModeAccess($data['mode_id'])) {
            abort(403, 'Unauthorized. You cannot create categories in a shopping mode you do not have permission to manage.');
        }

        // Auto-generate slug if empty
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Auto inherit mode_id from parent if subcategory and mode_id wasn't explicitly chosen
        if (!empty($data['parent_id']) && empty($data['mode_id'])) {
            $parent = Category::find($data['parent_id']);
            if ($parent?->mode_id) {
                $data['mode_id'] = $parent->mode_id;
            }
        }

        // Handle Image Upload to Cloudinary
        if ($request->hasFile('image')) {
            try {
                $data['image'] = $this->cloudinaryService->uploadCategoryImage($request->file('image'));
            } catch (Exception $e) {
                Log::error('Category Image Upload Failed', ['error' => $e->getMessage()]);
                return back()->withErrors(['image' => 'Image upload to Cloudinary failed: ' . $e->getMessage()])->withInput();
            }
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
        $admin = auth('admin')->user();
        if ($admin && $category->mode_id && !$admin->hasModeAccess($category->mode_id)) {
            abort(403, 'Unauthorized. You do not have permission to access categories in this shopping mode.');
        }

        $modes = $admin ? $admin->getAllowedModes() : Mode::active()->ordered()->get();
        $rootCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->when($admin && !$admin->isSuperAdmin(), fn ($q) => $q->whereIn('mode_id', $admin->getAllowedModeIds()))
            ->orderBy('name', 'asc')
            ->get();
        $parentCategories = $rootCategories;

        return view('admin.categories.edit', compact('category', 'rootCategories', 'parentCategories', 'modes'));
    }

    /**
     * Update the specified category in database.
     */
    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $admin = auth('admin')->user();
        if ($admin && $category->mode_id && !$admin->hasModeAccess($category->mode_id)) {
            abort(403, 'Unauthorized. You do not have permission to access categories in this shopping mode.');
        }

        $data = $request->validated();

        if ($admin && !empty($data['mode_id']) && !$admin->hasModeAccess($data['mode_id'])) {
            abort(403, 'Unauthorized. You cannot assign categories to a shopping mode you do not have permission to manage.');
        }

        // Auto-generate slug if empty
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Handle Image Upload / Replacement in Cloudinary
        if ($request->hasFile('image')) {
            try {
                if ($category->image) {
                    $this->deleteCategoryImage($category->image);
                }
                $data['image'] = $this->cloudinaryService->uploadCategoryImage($request->file('image'));
            } catch (Exception $e) {
                Log::error('Category Image Upload Failed', ['error' => $e->getMessage()]);
                return back()->withErrors(['image' => 'Image upload to Cloudinary failed: ' . $e->getMessage()])->withInput();
            }
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', "Category '{$category->name}' has been updated successfully.");
    }

    /**
     * Remove the specified category from database safely.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $admin = auth('admin')->user();
        if ($admin && $category->mode_id && !$admin->hasModeAccess($category->mode_id)) {
            abort(403, 'Unauthorized. You do not have permission to access categories in this shopping mode.');
        }

        // Check if category has subcategories or products
        if ($category->hasChildren()) {
            return back()->with('error', "Category '{$category->name}' cannot be deleted because it contains child categories. Please reassign or delete the subcategories first.");
        }

        if ($category->hasProducts()) {
            return back()->with('error', "Category '{$category->name}' cannot be deleted because it has associated products. Please reassign the products first.");
        }

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
     * Toggle status (Active / Inactive) via 1-click switch.
     */
    public function toggleStatus(Category $category, Request $request): RedirectResponse|JsonResponse
    {
        $admin = auth('admin')->user();
        if ($admin && $category->mode_id && !$admin->hasModeAccess($category->mode_id)) {
            abort(403, 'Unauthorized. You do not have permission to access categories in this shopping mode.');
        }

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
        $admin = auth('admin')->user();
        if ($admin && $category->mode_id && !$admin->hasModeAccess($category->mode_id)) {
            abort(403, 'Unauthorized. You do not have permission to access categories in this shopping mode.');
        }

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

    /**
     * Helper to safely remove category image from Cloudinary or local storage.
     */
    protected function deleteCategoryImage(string $imagePath): void
    {
        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://') || str_starts_with($imagePath, 'shopy_so/category')) {
            $this->cloudinaryService->deleteImage($imagePath);
        } elseif (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}

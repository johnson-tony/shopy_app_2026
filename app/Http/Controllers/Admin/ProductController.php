<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Mode;
use App\Models\Product;
use App\Services\CloudinaryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    protected CloudinaryService $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Display a listing of products with advanced filters and search.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $modeFilter = $request->input('mode');
        $categoryFilter = $request->input('category');
        $statusFilter = $request->input('status');
        $featuredFilter = $request->input('featured');

        $query = Product::with(['mode', 'category.parent']);

        // Search Keyword Filter
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // Mode Filter
        if ($modeFilter !== null && $modeFilter !== '') {
            $query->where('mode_id', (int) $modeFilter);
        }

        // Category Filter
        if ($categoryFilter !== null && $categoryFilter !== '') {
            $query->where('category_id', (int) $categoryFilter);
        }

        // Status Filter
        if ($statusFilter === 'active' || $statusFilter === '1') {
            $query->where('status', true);
        } elseif ($statusFilter === 'inactive' || $statusFilter === '0') {
            $query->where('status', false);
        }

        // Featured Filter
        if ($featuredFilter === 'featured' || $featuredFilter === '1') {
            $query->where('featured', true);
        } elseif ($featuredFilter === 'standard' || $featuredFilter === '0') {
            $query->where('featured', false);
        }

        $products = $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Top Metrics Cards
        $stats = [
            'total' => Product::count(),
            'active' => Product::where('status', true)->count(),
            'out_of_stock' => Product::where('stock', '<=', 0)->count(),
            'featured' => Product::where('featured', true)->count(),
        ];

        $modes = Mode::active()->ordered()->get();
        $categories = Category::with('parent')
            ->when($modeFilter, fn ($q) => $q->where('mode_id', (int) $modeFilter))
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.products.index', compact(
            'products',
            'stats',
            'modes',
            'categories',
            'search',
            'modeFilter',
            'categoryFilter',
            'statusFilter',
            'featuredFilter'
        ));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $modes = Mode::active()->ordered()->get();
        $categories = Category::with('parent')->orderBy('name', 'asc')->get();

        return view('admin.products.create', compact('modes', 'categories'));
    }

    /**
     * Store a newly created product in database.
     */
    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Auto generate unique slug if empty
        if (empty($data['slug'])) {
            $data['slug'] = Product::generateUniqueSlug($data['name']);
        }

        // Handle Cloudinary Image Upload to shopy_so/products
        if ($request->hasFile('image')) {
            try {
                $data['image'] = $this->cloudinaryService->uploadProductImage($request->file('image'));
            } catch (Exception $e) {
                Log::error('Product Image Upload Failed', ['error' => $e->getMessage()]);
                return back()
                    ->withErrors(['image' => 'Image upload to Cloudinary failed: ' . $e->getMessage()])
                    ->withInput();
            }
        }

        $product = Product::create($data);

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$product->name}' has been created successfully.");
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): View
    {
        $product->load(['mode', 'category.parent']);

        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $modes = Mode::active()->ordered()->get();
        $categories = Category::with('parent')->orderBy('name', 'asc')->get();

        return view('admin.products.edit', compact('product', 'modes', 'categories'));
    }

    /**
     * Update the specified product in database.
     */
    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        // Auto generate unique slug if empty
        if (empty($data['slug'])) {
            $data['slug'] = Product::generateUniqueSlug($data['name'], $product->id);
        }

        // Handle Image Upload / Replacement in Cloudinary
        if ($request->hasFile('image')) {
            try {
                if ($product->image) {
                    $this->deleteProductImage($product->image);
                }
                $data['image'] = $this->cloudinaryService->uploadProductImage($request->file('image'));
            } catch (Exception $e) {
                Log::error('Product Image Upload Failed', ['error' => $e->getMessage()]);
                return back()
                    ->withErrors(['image' => 'Image upload to Cloudinary failed: ' . $e->getMessage()])
                    ->withInput();
            }
        }

        $product->update($data);

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$product->name}' has been updated successfully.");
    }

    /**
     * Remove the specified product from database safely.
     */
    public function destroy(Product $product): RedirectResponse
    {
        if ($product->hasRelatedRecords()) {
            return back()->with('error', "Product '{$product->name}' cannot be deleted because it is referenced by existing orders or shopping carts.");
        }

        $name = $product->name;

        // Delete associated image from Cloudinary or local storage
        if ($product->image) {
            $this->deleteProductImage($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$name}' has been deleted successfully.");
    }

    /**
     * Toggle active status via 1-click switch.
     */
    public function toggleStatus(Product $product, Request $request): RedirectResponse|JsonResponse
    {
        $product->status = !$product->status;
        $product->save();

        $statusText = $product->status ? 'activated' : 'deactivated';
        $message = "Product '{$product->name}' has been {$statusText}.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $product->status,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Toggle featured status via 1-click badge switch.
     */
    public function toggleFeatured(Product $product, Request $request): RedirectResponse|JsonResponse
    {
        $product->featured = !$product->featured;
        $product->save();

        $featuredText = $product->featured ? 'marked as featured' : 'unmarked from featured';
        $message = "Product '{$product->name}' has been {$featuredText}.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'featured' => $product->featured,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * API endpoint to get categories by mode ID for dynamic UI cascading dropdown.
     */
    public function getCategoriesByMode(Request $request): JsonResponse
    {
        $modeId = $request->input('mode_id');

        $categories = Category::where('mode_id', $modeId)
            ->where('status', true)
            ->with('parent')
            ->orderBy('name', 'asc')
            ->get(['id', 'parent_id', 'name', 'slug']);

        return response()->json([
            'success' => true,
            'categories' => $categories,
        ]);
    }

    /**
     * Helper to safely remove product image from Cloudinary or local storage.
     */
    protected function deleteProductImage(string $imagePath): void
    {
        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://') || str_starts_with($imagePath, 'shopy_so/products')) {
            $this->cloudinaryService->deleteImage($imagePath);
        } elseif (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Mode;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the public storefront home page.
     */
    public function index(Request $request): View
    {
        // 1. Fetch all active shopping modes
        $modes = Mode::active()->ordered()->get();

        // 2. Determine selected mode filter (if requested)
        $selectedModeSlug = $request->query('mode');
        $currentMode = $selectedModeSlug ? $modes->firstWhere('slug', $selectedModeSlug) : null;

        // 3. Fetch top root categories for "Shop by Category" section
        $topCategories = Category::active()
            ->whereNull('parent_id')
            ->when($currentMode, fn ($q) => $q->where('mode_id', $currentMode->id))
            ->with(['mode', 'children' => fn ($q) => $q->active()])
            ->orderBy('sort_order', 'asc')
            ->get();

        // 4. Fetch category tabs for product section
        $tabCategories = Category::active()
            ->whereNull('parent_id')
            ->when($currentMode, fn ($q) => $q->where('mode_id', $currentMode->id))
            ->whereHas('products', fn ($q) => $q->active())
            ->orderBy('sort_order', 'asc')
            ->take(8)
            ->get();

        // 5. Selected Category tab (if requested)
        $selectedCategorySlug = $request->query('category');
        $selectedCategory = $selectedCategorySlug ? Category::where('slug', $selectedCategorySlug)->first() : null;

        // 6. Products query
        $products = Product::active()
            ->with(['category', 'mode'])
            ->when($currentMode, fn ($q) => $q->where('mode_id', $currentMode->id))
            ->when($selectedCategory, fn ($q) => $q->where('category_id', $selectedCategory->id))
            ->latest()
            ->take(16)
            ->get();

        // 7. Featured Best-Value Products
        $featuredProducts = Product::active()
            ->featured()
            ->with(['category', 'mode'])
            ->when($currentMode, fn ($q) => $q->where('mode_id', $currentMode->id))
            ->latest()
            ->take(8)
            ->get();

        return view('user.pages.home', compact(
            'modes',
            'currentMode',
            'topCategories',
            'tabCategories',
            'selectedCategory',
            'products',
            'featuredProducts'
        ));
    }

    /**
     * Fetch products by category via AJAX for smooth tab switching.
     */
    public function ajaxCategoryProducts(Request $request, ?string $slug = null): JsonResponse
    {
        $category = $slug ? Category::where('slug', $slug)->first() : null;
        $modeSlug = $request->query('mode');
        $mode = $modeSlug ? Mode::where('slug', $modeSlug)->first() : null;

        $products = Product::active()
            ->with(['category', 'mode'])
            ->when($mode, fn ($q) => $q->where('mode_id', $mode->id))
            ->when($category, fn ($q) => $q->where('category_id', $category->id))
            ->latest()
            ->take(12)
            ->get();

        $html = view('user.components.storefront.product-grid', ['products' => $products])->render();

        return response()->json([
            'success' => true,
            'count' => $products->count(),
            'html' => $html,
        ]);
    }
}

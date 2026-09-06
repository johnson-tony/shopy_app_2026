<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RestaurantRequest;
use App\Models\Restaurant;
use App\Services\CloudinaryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RestaurantController extends Controller
{
    protected CloudinaryService $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Display a listing of restaurants with search and filters.
     */
    public function index(Request $request): View
    {
        $admin = auth('admin')->user();

        // If sub-admin does not have food mode access, abort
        $foodMode = \App\Models\Mode::where('slug', 'food')->first();
        if ($admin && !$admin->isSuperAdmin() && $foodMode && !$admin->hasModeAccess($foodMode->id)) {
            abort(403, 'Unauthorized. You do not have permission to manage food delivery restaurants.');
        }

        $search = $request->string('search')->trim()->toString();
        $statusFilter = $request->input('status');
        $vegFilter = $request->input('veg');
        $featuredFilter = $request->input('featured');

        $query = Restaurant::withCount('products');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('cuisine', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($statusFilter === 'active' || $statusFilter === '1') {
            $query->where('status', true);
        } elseif ($statusFilter === 'inactive' || $statusFilter === '0') {
            $query->where('status', false);
        }

        if ($vegFilter === 'veg') {
            $query->where('is_pure_veg', true);
        } elseif ($vegFilter === 'non_veg') {
            $query->where('is_pure_veg', false);
        }

        if ($featuredFilter === '1' || $featuredFilter === 'yes') {
            $query->where('is_featured', true);
        }

        $restaurants = $query->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total'     => Restaurant::count(),
            'active'    => Restaurant::where('status', true)->count(),
            'pure_veg'  => Restaurant::where('is_pure_veg', true)->count(),
            'featured'  => Restaurant::where('is_featured', true)->count(),
        ];

        return view('admin.pages.restaurants.index', compact(
            'restaurants',
            'stats',
            'search',
            'statusFilter',
            'vegFilter',
            'featuredFilter'
        ));
    }

    /**
     * Show the form for creating a new restaurant.
     */
    public function create(): View
    {
        return view('admin.pages.restaurants.create');
    }

    /**
     * Store a newly created restaurant in storage.
     */
    public function store(RestaurantRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Upload Logo Image
        if ($request->hasFile('image')) {
            try {
                $uploadResult = $this->cloudinaryService->upload(
                    $request->file('image'),
                    'restaurants',
                    400,
                    400
                );
                $data['image'] = $uploadResult['secure_url'];
            } catch (Exception $e) {
                Log::warning('Cloudinary restaurant logo upload failed, falling back to local: ' . $e->getMessage());
                $data['image'] = $request->file('image')->store('restaurants', 'public');
            }
        }

        // Upload Banner Image
        if ($request->hasFile('banner_image')) {
            try {
                $uploadResult = $this->cloudinaryService->upload(
                    $request->file('banner_image'),
                    'restaurants/banners',
                    1200,
                    400
                );
                $data['banner_image'] = $uploadResult['secure_url'];
            } catch (Exception $e) {
                Log::warning('Cloudinary restaurant banner upload failed, falling back to local: ' . $e->getMessage());
                $data['banner_image'] = $request->file('banner_image')->store('restaurants/banners', 'public');
            }
        }

        Restaurant::create($data);

        return redirect()->route('admin.restaurants.index')
            ->with('success', "Restaurant '{$data['name']}' has been registered successfully.");
    }

    /**
     * Show the form for editing the specified restaurant.
     */
    public function edit(Restaurant $restaurant): View
    {
        return view('admin.pages.restaurants.edit', compact('restaurant'));
    }

    /**
     * Update the specified restaurant in storage.
     */
    public function update(RestaurantRequest $request, Restaurant $restaurant): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Logo Image Update
        if ($request->hasFile('image')) {
            try {
                $uploadResult = $this->cloudinaryService->upload(
                    $request->file('image'),
                    'restaurants',
                    400,
                    400
                );
                $data['image'] = $uploadResult['secure_url'];
            } catch (Exception $e) {
                Log::warning('Cloudinary restaurant logo upload failed, falling back to local: ' . $e->getMessage());
                $data['image'] = $request->file('image')->store('restaurants', 'public');
            }
        }

        // Banner Image Update
        if ($request->hasFile('banner_image')) {
            try {
                $uploadResult = $this->cloudinaryService->upload(
                    $request->file('banner_image'),
                    'restaurants/banners',
                    1200,
                    400
                );
                $data['banner_image'] = $uploadResult['secure_url'];
            } catch (Exception $e) {
                Log::warning('Cloudinary restaurant banner upload failed, falling back to local: ' . $e->getMessage());
                $data['banner_image'] = $request->file('banner_image')->store('restaurants/banners', 'public');
            }
        }

        $restaurant->update($data);

        return redirect()->route('admin.restaurants.index')
            ->with('success', "Restaurant '{$restaurant->name}' has been updated successfully.");
    }

    /**
     * Remove the specified restaurant from storage.
     */
    public function destroy(Restaurant $restaurant): RedirectResponse
    {
        $name = $restaurant->name;

        // Dissociate dishes so they don't orphan
        $restaurant->products()->update(['restaurant_id' => null]);
        $restaurant->delete();

        return redirect()->route('admin.restaurants.index')
            ->with('success', "Restaurant '{$name}' has been removed.");
    }

    /**
     * Toggle the status (active / inactive) of the restaurant via AJAX or form.
     */
    public function toggleStatus(Restaurant $restaurant, Request $request): JsonResponse|RedirectResponse
    {
        $restaurant->update([
            'status' => !$restaurant->status,
        ]);

        $statusText = $restaurant->status ? 'activated' : 'deactivated';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status'  => $restaurant->status,
                'message' => "Restaurant '{$restaurant->name}' is now {$statusText}.",
            ]);
        }

        return redirect()->back()->with('success', "Restaurant '{$restaurant->name}' is now {$statusText}.");
    }
}

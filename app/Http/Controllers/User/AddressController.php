<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AddressRequest;
use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddressController extends Controller
{
    /**
     * Display a listing of the authenticated user's saved addresses.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $addresses = $user->addresses()
            ->orderByDesc('is_default')
            ->latest('id')
            ->get();

        return view('user.pages.addresses.index', compact('addresses'));
    }

    /**
     * Show the form for creating a new address.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        return view('user.pages.addresses.create', compact('user'));
    }

    /**
     * Store a newly created address for the authenticated user.
     */
    public function store(AddressRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $hasAddresses = $user->addresses()->exists();
        $isDefault = !$hasAddresses || (bool) ($validated['is_default'] ?? false);

        if ($isDefault && $hasAddresses) {
            $user->addresses()->update(['is_default' => false]);
        }

        $validated['is_default'] = $isDefault;
        $user->addresses()->create($validated);

        return redirect()->route('user.addresses.index')->with('success', 'Address added successfully.');
    }

    /**
     * Show the form for editing the specified address.
     */
    public function edit(Request $request, UserAddress $address): View
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized access to this address.');
        }

        return view('user.pages.addresses.edit', compact('address'));
    }

    /**
     * Update the specified address for the authenticated user.
     */
    public function update(AddressRequest $request, UserAddress $address): RedirectResponse
    {
        $user = $request->user();

        if ($address->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this address.');
        }

        $validated = $request->validated();
        $isDefault = (bool) ($validated['is_default'] ?? false);

        if ($isDefault) {
            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        } elseif ($address->is_default) {
            $hasOther = $user->addresses()->where('id', '!=', $address->id)->exists();
            if (!$hasOther) {
                $isDefault = true;
            }
        }

        $validated['is_default'] = $isDefault;
        $address->update($validated);

        return redirect()->route('user.addresses.index')->with('success', 'Address updated successfully.');
    }

    /**
     * Remove the specified address for the authenticated user.
     */
    public function destroy(Request $request, UserAddress $address): RedirectResponse
    {
        $user = $request->user();

        if ($address->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this address.');
        }

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $firstAddress = $user->addresses()->first();
            if ($firstAddress) {
                $firstAddress->update(['is_default' => true]);
            }
        }

        return redirect()->route('user.addresses.index')->with('success', 'Address deleted successfully.');
    }

    /**
     * Set the specified address as the default address for the authenticated user.
     */
    public function setDefault(Request $request, UserAddress $address): RedirectResponse
    {
        $user = $request->user();

        if ($address->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this address.');
        }

        $user->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return redirect()->route('user.addresses.index')->with('success', 'Default address updated successfully.');
    }

    /**
     * Reverse-geocode GPS latitude/longitude using Google Geocoding API.
     */
    public function reverseGeocode(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $apiKey = config('services.google.geocoding_key') ?: config('services.google.maps_js_key');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Google Geocoding API key is not configured in .env',
            ], 500);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(6)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'latlng' => "{$validated['latitude']},{$validated['longitude']}",
                'key' => $apiKey,
            ]);

            if ($response->failed() || $response->json('status') !== 'OK') {
                return response()->json([
                    'success' => false,
                    'message' => $response->json('error_message') ?? 'Could not resolve location coordinates.',
                ], 422);
            }

            $result = $response->json('results.0');
            $components = $result['address_components'] ?? [];

            $getComponent = function (array $types) use ($components) {
                foreach ($components as $component) {
                    if (!empty(array_intersect($types, $component['types'] ?? []))) {
                        return $component['long_name'] ?? '';
                    }
                }
                return '';
            };

            $streetNumber = $getComponent(['street_number']);
            $route = $getComponent(['route']);
            $premise = $getComponent(['premise', 'subpremise', 'point_of_interest']);
            $sublocality2 = $getComponent(['sublocality_level_2']);
            $sublocality1 = $getComponent(['sublocality_level_1', 'neighborhood']);
            $locality = $getComponent(['locality', 'postal_town']);
            $city = $locality ?: $getComponent(['administrative_area_level_2']);
            $state = $getComponent(['administrative_area_level_1']);
            $postalCode = $getComponent(['postal_code']);
            $country = $getComponent(['country']) ?: 'India';

            $line1Parts = array_filter([$premise, trim($streetNumber . ' ' . $route)]);
            $addressLine1 = implode(', ', $line1Parts) ?: ($sublocality1 ?: ($result['formatted_address'] ?? ''));

            $line2Parts = array_filter([$sublocality2, ($line1Parts ? $sublocality1 : '')]);
            $addressLine2 = implode(', ', array_unique($line2Parts));

            return response()->json([
                'success' => true,
                'data' => [
                    'address_line1' => $addressLine1,
                    'address_line2' => $addressLine2,
                    'city' => $city,
                    'state' => $state,
                    'postal_code' => $postalCode,
                    'country' => $country,
                    'latitude' => (float) $validated['latitude'],
                    'longitude' => (float) $validated['longitude'],
                    'formatted_address' => $result['formatted_address'] ?? '',
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to Google Location Services.',
            ], 500);
        }
    }
}

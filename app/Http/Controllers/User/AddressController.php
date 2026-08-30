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
}

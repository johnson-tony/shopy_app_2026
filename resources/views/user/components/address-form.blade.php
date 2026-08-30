@props([
    'action',
    'method' => 'POST',
    'address' => null,
    'user' => null,
    'submitText' => 'Save Address',
])

@php
    $selectedType = old('address_type', $address->address_type ?? 'home');
    $fullName = old('full_name', $address->full_name ?? ($user->name ?? ''));
    $phone = old('phone', $address->phone ?? ($user->phone ?? ''));
    $addressLine1 = old('address_line1', $address->address_line1 ?? '');
    $addressLine2 = old('address_line2', $address->address_line2 ?? '');
    $landmark = old('landmark', $address->landmark ?? '');
    $city = old('city', $address->city ?? '');
    $state = old('state', $address->state ?? '');
    $postalCode = old('postal_code', $address->postal_code ?? '');
    $country = old('country', $address->country ?? 'India');
    $isDefault = (bool) old('is_default', $address->is_default ?? false);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if(strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <!-- Address Type Selector -->
    <div>
        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
            Address Type
        </label>
        <div class="grid grid-cols-3 gap-3">
            <label class="relative flex items-center justify-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-300 transition has-checked:border-indigo-600 has-checked:bg-indigo-50/50 has-checked:text-indigo-700 font-semibold text-sm">
                <input type="radio" name="address_type" value="home" class="sr-only" {{ strtolower($selectedType) === 'home' ? 'checked' : '' }}>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Home
                </span>
            </label>

            <label class="relative flex items-center justify-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-300 transition has-checked:border-indigo-600 has-checked:bg-indigo-50/50 has-checked:text-indigo-700 font-semibold text-sm">
                <input type="radio" name="address_type" value="work" class="sr-only" {{ strtolower($selectedType) === 'work' ? 'checked' : '' }}>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    Work
                </span>
            </label>

            <label class="relative flex items-center justify-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-300 transition has-checked:border-indigo-600 has-checked:bg-indigo-50/50 has-checked:text-indigo-700 font-semibold text-sm">
                <input type="radio" name="address_type" value="other" class="sr-only" {{ strtolower($selectedType) === 'other' ? 'checked' : '' }}>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                    Other
                </span>
            </label>
        </div>
        @error('address_type')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Contact Information: Full Name & Phone -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="full_name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Full Name <span class="text-rose-500">*</span>
            </label>
            <input id="full_name" type="text" name="full_name" value="{{ $fullName }}" required
                placeholder="Recipient full name"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('full_name') border-rose-500 @enderror">
            @error('full_name')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="phone" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Phone Number
            </label>
            <input id="phone" type="tel" name="phone" value="{{ $phone }}"
                placeholder="+91 98765 43210"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('phone') border-rose-500 @enderror">
            @error('phone')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Address Line 1 -->
    <div>
        <label for="address_line1" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
            Address Line 1 (Flat, House No., Building, Company, Street) <span class="text-rose-500">*</span>
        </label>
        <input id="address_line1" type="text" name="address_line1" value="{{ $addressLine1 }}" required
            placeholder="e.g. 123 Main Street, Apt 4B"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('address_line1') border-rose-500 @enderror">
        @error('address_line1')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Address Line 2 & Landmark -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="address_line2" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Address Line 2 (Area, Colony, Sector) <span class="text-slate-400 font-normal">(Optional)</span>
            </label>
            <input id="address_line2" type="text" name="address_line2" value="{{ $addressLine2 }}"
                placeholder="e.g. Indiranagar"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('address_line2') border-rose-500 @enderror">
            @error('address_line2')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="landmark" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Landmark <span class="text-slate-400 font-normal">(Optional)</span>
            </label>
            <input id="landmark" type="text" name="landmark" value="{{ $landmark }}"
                placeholder="e.g. Near Metro Station"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('landmark') border-rose-500 @enderror">
            @error('landmark')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- City, State, Postal Code -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label for="city" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                City <span class="text-rose-500">*</span>
            </label>
            <input id="city" type="text" name="city" value="{{ $city }}" required
                placeholder="e.g. Bengaluru"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('city') border-rose-500 @enderror">
            @error('city')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="state" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                State <span class="text-rose-500">*</span>
            </label>
            <input id="state" type="text" name="state" value="{{ $state }}" required
                placeholder="e.g. Karnataka"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('state') border-rose-500 @enderror">
            @error('state')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="postal_code" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Postal Code / PIN <span class="text-rose-500">*</span>
            </label>
            <input id="postal_code" type="text" name="postal_code" value="{{ $postalCode }}" required
                placeholder="e.g. 560001"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('postal_code') border-rose-500 @enderror">
            @error('postal_code')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Country -->
    <div>
        <label for="country" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
            Country <span class="text-rose-500">*</span>
        </label>
        <input id="country" type="text" name="country" value="{{ $country }}" required
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('country') border-rose-500 @enderror">
        @error('country')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Make Default Checkbox -->
    <div class="pt-2">
        <label class="inline-flex items-center gap-2.5 cursor-pointer">
            <input type="checkbox" name="is_default" value="1" {{ $isDefault ? 'checked' : '' }}
                class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
            <span class="text-sm font-medium text-slate-700">Make this my default delivery address</span>
        </label>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
        <a href="{{ route('user.addresses.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-2.5 rounded-xl text-white font-semibold bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 transition shadow-sm text-sm">
            {{ $submitText }}
        </button>
    </div>
</form>

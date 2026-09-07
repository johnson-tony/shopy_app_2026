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
    $latitude = old('latitude', $address->latitude ?? '');
    $longitude = old('longitude', $address->longitude ?? '');
    $isDefault = (bool) old('is_default', $address->is_default ?? false);
    $formId = 'addr_form_' . ($address->id ?? 'new_' . uniqid());
    $googleMapsKey = config('services.google.maps_js_key') ?: config('services.google.places_key');
@endphp

<form id="{{ $formId }}" method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if(strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <!-- Hidden Lat/Lng fields -->
    <input type="hidden" name="latitude" id="{{ $formId }}_latitude" value="{{ $latitude }}">
    <input type="hidden" name="longitude" id="{{ $formId }}_longitude" value="{{ $longitude }}">
    @if(request('return_to'))
        <input type="hidden" name="return_to" value="{{ request('return_to') }}">
    @endif

    <!-- Google Location Options Banner (Places Autocomplete + GPS Use My Location) -->
    <div class="bg-gradient-to-r from-indigo-50/80 to-blue-50/80 rounded-2xl p-4 sm:p-5 border border-indigo-100 space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </span>
                <div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Fast Fill With Google Location</h3>
                    <p class="text-xs text-slate-500">Auto-detect GPS or search your location to fill fields automatically</p>
                </div>
            </div>

            <!-- Option 12: Use My Current Location Button -->
            <div>
                <button type="button" onclick="detectUserCurrentLocation('{{ $formId }}')"
                    id="{{ $formId }}_gps_btn"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-white text-indigo-700 hover:bg-indigo-50 border border-indigo-200 shadow-xs hover:shadow transition cursor-pointer">
                    <svg id="{{ $formId }}_gps_icon" class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0013 3.06V1h-2v2.06A8.994 8.994 0 003.06 11H1v2h2.06A8.994 8.994 0 0011 20.94V23h2v-2.06A8.994 8.994 0 0020.94 13H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"></path>
                    </svg>
                    <span id="{{ $formId }}_gps_text">Use My Current Location</span>
                </button>
            </div>
        </div>

        <!-- Option 1: Google Places Autocomplete Search Box -->
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text" id="{{ $formId }}_places_search"
                placeholder="Search area, apartment, street or landmark (e.g. Indiranagar, Bengaluru)..."
                autocomplete="off"
                class="w-full pl-10 pr-4 py-2.5 bg-white rounded-xl border border-indigo-200 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-xs transition">
        </div>
        
        <!-- Live Geocode Status Alert (Hidden by default) -->
        <div id="{{ $formId }}_geo_status" class="hidden text-xs rounded-lg px-3 py-1.5 font-medium"></div>
    </div>

    <!-- Address Type Selector -->
    <div>
        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
            Address Type
        </label>
        <div class="grid grid-cols-3 gap-3">
            <!-- Home Option -->
            <label class="relative flex items-center justify-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-300 transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 has-[:checked]:text-indigo-700 has-checked:border-indigo-600 has-checked:bg-indigo-50/50 has-checked:text-indigo-700 font-semibold text-sm">
                <input type="radio" name="address_type" value="home" class="sr-only" {{ strtolower($selectedType) === 'home' ? 'checked' : '' }}>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Home
                </span>
            </label>

            <!-- Work Option -->
            <label class="relative flex items-center justify-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-300 transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 has-[:checked]:text-indigo-700 has-checked:border-indigo-600 has-checked:bg-indigo-50/50 has-checked:text-indigo-700 font-semibold text-sm">
                <input type="radio" name="address_type" value="work" class="sr-only" {{ strtolower($selectedType) === 'work' ? 'checked' : '' }}>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    Work
                </span>
            </label>

            <!-- Other Option -->
            <label class="relative flex items-center justify-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-300 transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 has-[:checked]:text-indigo-700 has-checked:border-indigo-600 has-checked:bg-indigo-50/50 has-checked:text-indigo-700 font-semibold text-sm">
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

    <!-- Form Fields Grid: 3 Inputs Per Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Row 1 - Col 1: Full Name -->
        <div>
            <label for="{{ $formId }}_full_name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Full Name <span class="text-rose-500">*</span>
            </label>
            <input id="{{ $formId }}_full_name" type="text" name="full_name" value="{{ $fullName }}" required
                placeholder="Recipient full name"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('full_name') border-rose-500 @enderror">
            @error('full_name')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Row 1 - Col 2: Phone Number -->
        <div>
            <label for="{{ $formId }}_phone" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Phone Number
            </label>
            <input id="{{ $formId }}_phone" type="tel" name="phone" value="{{ $phone }}"
                placeholder="+91 98765 43210"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('phone') border-rose-500 @enderror">
            @error('phone')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Row 1 - Col 3: Address Line 1 -->
        <div>
            <label for="{{ $formId }}_address_line1" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Address Line 1 <span class="text-rose-500">*</span>
            </label>
            <input id="{{ $formId }}_address_line1" type="text" name="address_line1" value="{{ $addressLine1 }}" required
                placeholder="Flat / House No. / Building"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('address_line1') border-rose-500 @enderror">
            @error('address_line1')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Row 2 - Col 1: Address Line 2 -->
        <div>
            <label for="{{ $formId }}_address_line2" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Address Line 2 <span class="text-slate-400 font-normal">(Optional)</span>
            </label>
            <input id="{{ $formId }}_address_line2" type="text" name="address_line2" value="{{ $addressLine2 }}"
                placeholder="Area / Colony / Street"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('address_line2') border-rose-500 @enderror">
            @error('address_line2')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Row 2 - Col 2: Landmark -->
        <div>
            <label for="{{ $formId }}_landmark" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Landmark <span class="text-slate-400 font-normal">(Optional)</span>
            </label>
            <input id="{{ $formId }}_landmark" type="text" name="landmark" value="{{ $landmark }}"
                placeholder="e.g. Near Metro Station"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('landmark') border-rose-500 @enderror">
            @error('landmark')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Row 2 - Col 3: City -->
        <div>
            <label for="{{ $formId }}_city" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                City <span class="text-rose-500">*</span>
            </label>
            <input id="{{ $formId }}_city" type="text" name="city" value="{{ $city }}" required
                placeholder="e.g. Bengaluru"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('city') border-rose-500 @enderror">
            @error('city')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Row 3 - Col 1: State -->
        <div>
            <label for="{{ $formId }}_state" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                State <span class="text-rose-500">*</span>
            </label>
            <input id="{{ $formId }}_state" type="text" name="state" value="{{ $state }}" required
                placeholder="e.g. Karnataka"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('state') border-rose-500 @enderror">
            @error('state')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Row 3 - Col 2: Postal Code / PIN -->
        <div>
            <label for="{{ $formId }}_postal_code" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Postal Code / PIN <span class="text-rose-500">*</span>
            </label>
            <input id="{{ $formId }}_postal_code" type="text" name="postal_code" value="{{ $postalCode }}" required
                placeholder="e.g. 560001"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('postal_code') border-rose-500 @enderror">
            @error('postal_code')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Row 3 - Col 3: Country -->
        <div>
            <label for="{{ $formId }}_country" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Country <span class="text-rose-500">*</span>
            </label>
            <input id="{{ $formId }}_country" type="text" name="country" value="{{ $country }}" required
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('country') border-rose-500 @enderror">
            @error('country')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
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

<script>
    // Autofill fields helper function
    function populateFormFields(formId, data) {
        if (data.address_line1) {
            const el = document.getElementById(formId + '_address_line1');
            if (el) el.value = data.address_line1;
        }
        if (data.address_line2) {
            const el = document.getElementById(formId + '_address_line2');
            if (el) el.value = data.address_line2;
        }
        if (data.city) {
            const el = document.getElementById(formId + '_city');
            if (el) el.value = data.city;
        }
        if (data.state) {
            const el = document.getElementById(formId + '_state');
            if (el) el.value = data.state;
        }
        if (data.postal_code) {
            const el = document.getElementById(formId + '_postal_code');
            if (el) el.value = data.postal_code;
        }
        if (data.country) {
            const el = document.getElementById(formId + '_country');
            if (el) el.value = data.country;
        }
        if (data.latitude) {
            const el = document.getElementById(formId + '_latitude');
            if (el) el.value = data.latitude;
        }
        if (data.longitude) {
            const el = document.getElementById(formId + '_longitude');
            if (el) el.value = data.longitude;
        }

        // Highlight fields smoothly
        [formId + '_address_line1', formId + '_city', formId + '_state', formId + '_postal_code'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.classList.add('ring-2', 'ring-emerald-400');
                setTimeout(() => el.classList.remove('ring-2', 'ring-emerald-400'), 1800);
            }
        });
    }

    // Option 12: Use My Current Location (GPS Auto-detect)
    function detectUserCurrentLocation(formId) {
        const btn = document.getElementById(formId + '_gps_btn');
        const btnText = document.getElementById(formId + '_gps_text');
        const statusBox = document.getElementById(formId + '_geo_status');

        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }

        btnText.innerText = 'Detecting GPS...';
        btn.disabled = true;
        btn.classList.add('opacity-75');

        navigator.geolocation.getCurrentPosition(
            async function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                btnText.innerText = 'Resolving Address...';

                try {
                    const response = await fetch('{{ route('user.addresses.reverseGeocode') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ latitude: lat, longitude: lng }),
                    });

                    const resData = await response.json();

                    if (resData.success && resData.data) {
                        populateFormFields(formId, resData.data);
                        if (statusBox) {
                            statusBox.className = 'text-xs rounded-lg px-3 py-2 font-medium bg-emerald-50 text-emerald-800 border border-emerald-200 block';
                            statusBox.innerHTML = '<strong>Location Detected:</strong> ' + (resData.data.formatted_address || (resData.data.city + ', ' + resData.data.state));
                        }
                    } else {
                        // Fallback: set coordinates only
                        populateFormFields(formId, { latitude: lat, longitude: lng });
                        if (statusBox) {
                            statusBox.className = 'text-xs rounded-lg px-3 py-2 font-medium bg-amber-50 text-amber-800 border border-amber-200 block';
                            statusBox.innerText = resData.message || 'Coordinates set. Please fill remaining address fields manually.';
                        }
                    }
                } catch (err) {
                    populateFormFields(formId, { latitude: lat, longitude: lng });
                    if (statusBox) {
                        statusBox.className = 'text-xs rounded-lg px-3 py-2 font-medium bg-amber-50 text-amber-800 border border-amber-200 block';
                        statusBox.innerText = 'GPS coordinates captured. Please complete address details.';
                    }
                } finally {
                    btnText.innerText = 'Use My Current Location';
                    btn.disabled = false;
                    btn.classList.remove('opacity-75');
                }
            },
            function (error) {
                btnText.innerText = 'Use My Current Location';
                btn.disabled = false;
                btn.classList.remove('opacity-75');

                let msg = 'Could not access GPS location.';
                if (error.code === error.PERMISSION_DENIED) {
                    msg = 'GPS permission denied. Please allow location access in browser or search your address below.';
                }
                if (statusBox) {
                    statusBox.className = 'text-xs rounded-lg px-3 py-2 font-medium bg-rose-50 text-rose-800 border border-rose-200 block';
                    statusBox.innerText = msg;
                } else {
                    alert(msg);
                }
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    // Option 1: Google Places Autocomplete Initialization
    function initGooglePlacesAutocomplete_{{ str_replace('-', '_', $formId) }}() {
        const input = document.getElementById('{{ $formId }}_places_search');
        if (!input || !window.google || !google.maps || !google.maps.places) return;

        const autocomplete = new google.maps.places.Autocomplete(input, {
            componentRestrictions: { country: ['in', 'us', 'ae', 'uk', 'ca'] },
            fields: ['address_components', 'formatted_address', 'geometry', 'name'],
        });

        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            if (!place.geometry) return;

            const components = place.address_components || [];
            const getComp = (types) => {
                for (const c of components) {
                    if (c.types.some(t => types.includes(t))) return c.long_name;
                }
                return '';
            };

            const streetNum = getComp(['street_number']);
            const route = getComp(['route']);
            const premise = getComp(['premise', 'subpremise', 'point_of_interest']);
            const sublocality2 = getComp(['sublocality_level_2']);
            const sublocality1 = getComp(['sublocality_level_1', 'neighborhood']);
            const locality = getComp(['locality', 'postal_town']);
            const city = locality || getComp(['administrative_area_level_2']);
            const state = getComp(['administrative_area_level_1']);
            const postalCode = getComp(['postal_code']);
            const country = getComp(['country']) || 'India';

            const line1Parts = [premise, [streetNum, route].filter(Boolean).join(' ')].filter(Boolean);
            const line1 = line1Parts.join(', ') || sublocality1 || place.name || place.formatted_address;
            const line2 = [sublocality2, line1Parts.length ? sublocality1 : ''].filter(Boolean).join(', ');

            populateFormFields('{{ $formId }}', {
                address_line1: line1,
                address_line2: line2,
                city: city,
                state: state,
                postal_code: postalCode,
                country: country,
                latitude: place.geometry.location.lat(),
                longitude: place.geometry.location.lng(),
            });

            const statusBox = document.getElementById('{{ $formId }}_geo_status');
            if (statusBox) {
                statusBox.className = 'text-xs rounded-lg px-3 py-2 font-medium bg-emerald-50 text-emerald-800 border border-emerald-200 block';
                statusBox.innerHTML = '<strong>Selected:</strong> ' + (place.formatted_address || place.name);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (window.google && window.google.maps && window.google.maps.places) {
            initGooglePlacesAutocomplete_{{ str_replace('-', '_', $formId) }}();
        } else if (!window.googleMapsScriptLoading) {
            window.googleMapsScriptLoading = true;
            const script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&libraries=places&callback=onGoogleMapsApiLoaded';
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
        }
    });

    window.onGoogleMapsApiLoaded = window.onGoogleMapsApiLoaded || function () {
        if (typeof initGooglePlacesAutocomplete_{{ str_replace('-', '_', $formId) }} === 'function') {
            initGooglePlacesAutocomplete_{{ str_replace('-', '_', $formId) }}();
        }
    };
</script>

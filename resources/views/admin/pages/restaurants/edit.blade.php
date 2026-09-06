@extends('admin.layouts.admin')

@section('title', 'Edit Restaurant Partner: ' . $restaurant->name)

@section('content')
<div class="space-y-6 max-w-5xl">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-xs font-semibold text-amber-400 mb-2">
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                <span>Food Delivery Partner</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Edit: {{ $restaurant->name }}</h1>
            <p class="text-slate-400 text-sm mt-1">
                Update restaurant profile, cuisine categories, delivery speed, and partner branding.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.restaurants.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-sm transition">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Back to List</span>
            </a>
        </div>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('admin.restaurants.update', $restaurant) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-5">
                    <h3 class="text-base font-bold text-white border-b border-slate-800 pb-3">Basic Information</h3>

                    <!-- Name -->
                    <div class="space-y-1.5">
                        <label for="name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Restaurant Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name', $restaurant->name) }}" 
                               required 
                               placeholder="e.g. Thalappakatti Biryani" 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500 @error('name') border-rose-500 @enderror">
                        @error('name')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Slug -->
                    <div class="space-y-1.5">
                        <label for="slug" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            URL Slug
                        </label>
                        <input type="text" 
                               name="slug" 
                               id="slug" 
                               value="{{ old('slug', $restaurant->slug) }}" 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500 font-mono @error('slug') border-rose-500 @enderror">
                        @error('slug')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cuisine -->
                    <div class="space-y-1.5">
                        <label for="cuisine" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Cuisine / Categories <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="cuisine" 
                               id="cuisine" 
                               value="{{ old('cuisine', $restaurant->cuisine) }}" 
                               required 
                               placeholder="e.g. South Indian, Biryani, Mughlai" 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500 @error('cuisine') border-rose-500 @enderror">
                        @error('cuisine')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Delivery Time & Cost for Two -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label for="delivery_time" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                                Avg. Delivery Time (Mins) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" 
                                   name="delivery_time" 
                                   id="delivery_time" 
                                   value="{{ old('delivery_time', $restaurant->delivery_time) }}" 
                                   min="5" 
                                   max="180" 
                                   required 
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500">
                        </div>
                        <div class="space-y-1.5">
                            <label for="cost_for_two" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                                Cost for Two (₹) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" 
                                   step="0.01" 
                                   name="cost_for_two" 
                                   id="cost_for_two" 
                                   value="{{ old('cost_for_two', $restaurant->cost_for_two) }}" 
                                   min="0" 
                                   required 
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500">
                        </div>
                    </div>

                    <!-- Address & City -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label for="address" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                                Outlet Address / Street
                            </label>
                            <input type="text" 
                                   name="address" 
                                   id="address" 
                                   value="{{ old('address', $restaurant->address) }}" 
                                   placeholder="e.g. 12 Anna Salai, T. Nagar" 
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500">
                        </div>
                        <div class="space-y-1.5">
                            <label for="city" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                                City
                            </label>
                            <input type="text" 
                                   name="city" 
                                   id="city" 
                                   value="{{ old('city', $restaurant->city) }}" 
                                   placeholder="Chennai" 
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500">
                        </div>
                    </div>
                </div>

                <!-- Media Section: Logo & Banner -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-5">
                    <h3 class="text-base font-bold text-white border-b border-slate-800 pb-3">Visuals &amp; Branding</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Logo Upload -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                                Logo Image (1:1 Square)
                            </label>
                            <div class="border-2 border-dashed border-slate-800 hover:border-amber-500/50 rounded-2xl p-4 text-center cursor-pointer transition bg-slate-950 relative overflow-hidden group">
                                <input type="file" name="image" id="restaurantLogoInput" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-10" onchange="previewRestaurantLogo(this);">
                                <div id="logoPreviewWrapper" class="{{ $restaurant->image ? '' : 'hidden' }} mb-2">
                                    <img id="logoPreviewImg" src="{{ $restaurant->image ? $restaurant->image_url : '#' }}" alt="Logo Preview" class="w-20 h-20 mx-auto rounded-xl object-cover border border-slate-700">
                                </div>
                                <div id="logoPlaceholder" class="space-y-1 py-3 {{ $restaurant->image ? 'hidden' : '' }}">
                                    <i class="fa-solid fa-cloud-arrow-up text-2xl text-slate-500 group-hover:text-amber-400 transition"></i>
                                    <p class="text-xs text-slate-400">Click to change logo</p>
                                    <p class="text-[10px] text-slate-500">PNG, JPG, WebP up to 3MB</p>
                                </div>
                            </div>
                        </div>

                        <!-- Banner Upload -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                                Banner Image (Wide 3:1)
                            </label>
                            <div class="border-2 border-dashed border-slate-800 hover:border-amber-500/50 rounded-2xl p-4 text-center cursor-pointer transition bg-slate-950 relative overflow-hidden group">
                                <input type="file" name="banner_image" id="restaurantBannerInput" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-10" onchange="previewRestaurantBanner(this);">
                                <div id="bannerPreviewWrapper" class="{{ $restaurant->banner_image ? '' : 'hidden' }} mb-2">
                                    <img id="bannerPreviewImg" src="{{ $restaurant->banner_image ? $restaurant->banner_url : '#' }}" alt="Banner Preview" class="w-full h-20 mx-auto rounded-xl object-cover border border-slate-700">
                                </div>
                                <div id="bannerPlaceholder" class="space-y-1 py-3 {{ $restaurant->banner_image ? 'hidden' : '' }}">
                                    <i class="fa-solid fa-image text-2xl text-slate-500 group-hover:text-amber-400 transition"></i>
                                    <p class="text-xs text-slate-400">Click to change banner</p>
                                    <p class="text-[10px] text-slate-500">PNG, JPG, WebP up to 4MB</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right 1 Col: Badges & Controls -->
            <div class="space-y-6">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-5">
                    <h3 class="text-base font-bold text-white border-b border-slate-800 pb-3">Settings &amp; Flags</h3>

                    <!-- Pure Veg Toggle -->
                    <label class="flex items-center justify-between p-3 rounded-2xl bg-slate-950 border border-slate-800 cursor-pointer hover:border-slate-700 transition">
                        <div>
                            <span class="text-sm font-bold text-white block">Pure Veg Only</span>
                            <span class="text-xs text-slate-500">100% vegetarian restaurant</span>
                        </div>
                        <input type="checkbox" name="is_pure_veg" value="1" {{ old('is_pure_veg', $restaurant->is_pure_veg) ? 'checked' : '' }} class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                    </label>

                    <!-- Featured Toggle -->
                    <label class="flex items-center justify-between p-3 rounded-2xl bg-slate-950 border border-slate-800 cursor-pointer hover:border-slate-700 transition">
                        <div>
                            <span class="text-sm font-bold text-white block">Featured Restaurant</span>
                            <span class="text-xs text-slate-500">Promoted on Food landing page</span>
                        </div>
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $restaurant->is_featured) ? 'checked' : '' }} class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500">
                    </label>

                    <!-- Status Toggle -->
                    <label class="flex items-center justify-between p-3 rounded-2xl bg-slate-950 border border-slate-800 cursor-pointer hover:border-slate-700 transition">
                        <div>
                            <span class="text-sm font-bold text-white block">Active Status</span>
                            <span class="text-xs text-slate-500">Allow customers to place orders</span>
                        </div>
                        <input type="checkbox" name="status" value="1" {{ old('status', $restaurant->status) ? 'checked' : '' }} class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500">
                    </label>

                    <!-- Rating Defaults -->
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-400 uppercase">Rating</label>
                            <input type="number" step="0.1" min="1" max="5" name="rating" value="{{ old('rating', $restaurant->rating) }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-400 uppercase">Ratings Count</label>
                            <input type="number" min="0" name="ratings_count" value="{{ old('ratings_count', $restaurant->ratings_count) }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="space-y-3">
                    <button type="submit" class="w-full py-3 px-6 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-sm shadow-lg shadow-amber-500/20 transition flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save Changes</span>
                    </button>
                    <a href="{{ route('admin.restaurants.index') }}" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-400 text-xs font-bold text-center block transition">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function previewRestaurantLogo(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('logoPreviewImg').src = e.target.result;
                document.getElementById('logoPreviewWrapper').classList.remove('hidden');
                document.getElementById('logoPlaceholder').classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewRestaurantBanner(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('bannerPreviewImg').src = e.target.result;
                document.getElementById('bannerPreviewWrapper').classList.remove('hidden');
                document.getElementById('bannerPlaceholder').classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
@endsection

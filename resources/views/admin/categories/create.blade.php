@extends('admin.layouts.admin')

@section('title', 'Create Category')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.categories.index') }}" class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl md:text-2xl font-black text-white tracking-tight">Create New Category</h1>
                <p class="text-slate-400 text-xs mt-0.5">Add a new department or subcategory to your storefront catalog.</p>
            </div>
        </div>
    </div>

    <!-- Category Form -->
    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Core Info & SEO (2 cols) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Details Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-5">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        General Information
                    </h2>

                    <!-- Category Name -->
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Category Name <span class="text-rose-400">*</span>
                        </label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required
                            placeholder="e.g. Consumer Electronics, Men's Fashion"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-rose-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Slug with Auto-Generator Helper -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="slug" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                                URL Slug <span class="text-slate-500 font-normal font-mono">(Auto-generated if empty)</span>
                            </label>
                            <button type="button" id="unlockSlugBtn" class="text-[11px] text-indigo-400 hover:text-indigo-300 transition">
                                Edit Custom Slug
                            </button>
                        </div>
                        <input id="slug" type="text" name="slug" value="{{ old('slug') }}"
                            placeholder="e.g. consumer-electronics"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('slug') border-rose-500 @enderror">
                        @error('slug')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Parent Category Selector -->
                    <div>
                        <label for="parent_id" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Parent Category <span class="text-slate-500 font-normal">(Leave empty for Root Category)</span>
                        </label>
                        <select id="parent_id" name="parent_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition @error('parent_id') border-rose-500 @enderror">
                            <option value="">None (Top-Level Root Category)</option>
                            @foreach ($parentCategories as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Description <span class="text-slate-500 font-normal">(Optional)</span>
                        </label>
                        <textarea id="description" name="description" rows="4" placeholder="Brief summary of items in this category..."
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-rose-500 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- SEO Metadata Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Search Engine Optimization (SEO)
                    </h2>

                    <div>
                        <label for="meta_title" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Meta Title <span class="text-slate-500 font-normal">(Optional)</span>
                        </label>
                        <input id="meta_title" type="text" name="meta_title" value="{{ old('meta_title') }}"
                            placeholder="SEO title tag"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="meta_description" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Meta Description <span class="text-slate-500 font-normal">(Optional)</span>
                        </label>
                        <textarea id="meta_description" name="meta_description" rows="2" placeholder="SEO meta description summary..."
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('meta_description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Right Column: Media, Order & Toggles (1 col) -->
            <div class="space-y-6">
                <!-- Media & Image Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Category Visuals
                    </h2>

                    <!-- Image File Upload with Live Preview -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Category Banner / Thumbnail
                        </label>

                        <div class="relative border-2 border-dashed border-slate-700 hover:border-indigo-500 rounded-2xl p-4 text-center cursor-pointer transition bg-slate-950" id="dropZone">
                            <input id="imageInput" type="file" name="image" accept="image/jpeg,image/png,image/jpg,image/webp,image/svg+xml" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div id="uploadPlaceholder" class="space-y-2">
                                <svg class="w-8 h-8 mx-auto text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-xs text-slate-400 font-medium">Click or drag image to upload</p>
                                <p class="text-[10px] text-slate-500">JPG, PNG, WebP up to 3MB</p>
                            </div>
                            <div id="imagePreviewContainer" class="hidden">
                                <img id="imagePreview" src="#" alt="Preview" class="w-full h-36 object-cover rounded-xl border border-slate-700">
                                <p class="text-[11px] text-indigo-400 font-medium mt-2">Click to replace image</p>
                            </div>
                        </div>
                        @error('image')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Icon Class -->
                    <div>
                        <label for="icon" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Icon Class <span class="text-slate-500 font-normal">(FontAwesome)</span>
                        </label>
                        <input id="icon" type="text" name="icon" value="{{ old('icon') }}"
                            placeholder="e.g. fas fa-laptop, fas fa-shirt"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Display Settings Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        Catalog Settings
                    </h2>

                    <!-- Display Sort Order -->
                    <div>
                        <label for="sort_order" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Display Sort Order
                        </label>
                        <input id="sort_order" type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="99999"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <p class="text-[11px] text-slate-500 mt-1">Lower numbers appear first on storefront navigation.</p>
                    </div>

                    <!-- Active Toggle -->
                    <div class="pt-2 border-t border-slate-800/80">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="status" value="1" {{ old('status', '1') == '1' ? 'checked' : '' }}
                                class="w-4 h-4 mt-0.5 rounded text-indigo-600 bg-slate-950 border-slate-700 focus:ring-indigo-500">
                            <div>
                                <span class="text-xs font-bold text-white">Active Status</span>
                                <p class="text-[11px] text-slate-400">When enabled, this category is visible to shoppers.</p>
                            </div>
                        </label>
                    </div>

                    <!-- Featured Toggle -->
                    <div class="pt-2 border-t border-slate-800/80">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') == '1' ? 'checked' : '' }}
                                class="w-4 h-4 mt-0.5 rounded text-amber-500 bg-slate-950 border-slate-700 focus:ring-amber-500">
                            <div>
                                <span class="text-xs font-bold text-white">Featured Category</span>
                                <p class="text-[11px] text-slate-400">Showcase this category on homepage quick links &amp; hero bar.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Submit Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-3">
                    <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-md shadow-indigo-600/30 transition cursor-pointer flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Save Category</span>
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="w-full block text-center py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        const imageInput = document.getElementById('imageInput');
        const uploadPlaceholder = document.getElementById('uploadPlaceholder');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        const imagePreview = document.getElementById('imagePreview');

        let autoSlug = true;

        // Auto-generate slug as user types name (unless manually edited)
        nameInput?.addEventListener('input', () => {
            if (autoSlug) {
                slugInput.value = nameInput.value
                    .toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        });

        slugInput?.addEventListener('input', () => {
            autoSlug = false;
        });

        // Live Image Preview
        imageInput?.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    imagePreview.src = event.target.result;
                    uploadPlaceholder.classList.add('hidden');
                    imagePreviewContainer.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>
@endsection

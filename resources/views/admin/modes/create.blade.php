@extends('admin.layouts.admin')

@section('title', 'Create Mode')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.modes.index') }}" class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl md:text-2xl font-black text-white tracking-tight">Create New Shopping Mode</h1>
                <p class="text-slate-400 text-xs mt-0.5">Define a shopping or service channel for your storefront architecture.</p>
            </div>
        </div>
    </div>

    <!-- Mode Form -->
    <form method="POST" action="{{ route('admin.modes.store') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Core Info (2 cols) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Details Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-5">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        General Information
                    </h2>

                    <!-- Mode Name -->
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Mode Name <span class="text-rose-400">*</span>
                        </label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required
                            placeholder="e.g. Shopy, Food, Minutes, Pharmacy"
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
                            placeholder="e.g. shopy, food, minutes"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('slug') border-rose-500 @enderror">
                        @error('slug')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Description <span class="text-slate-500 font-normal">(Optional)</span>
                        </label>
                        <textarea id="description" name="description" rows="3"
                            placeholder="Brief description of this shopping or service channel..."
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-rose-500 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Right Column: Settings & Icon (1 col) -->
            <div class="space-y-6">
                <!-- Status & Display Settings -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-5">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        Channel Settings
                    </h2>

                    <!-- Active Status Checkbox -->
                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-950 border border-slate-800">
                        <div class="flex items-center h-5">
                            <input id="status" type="checkbox" name="status" value="1" {{ old('status', '1') == '1' ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </div>
                        <div>
                            <label for="status" class="text-xs font-semibold text-slate-200 block cursor-pointer">
                                Active Mode
                            </label>
                            <p class="text-[11px] text-slate-400 mt-0.5">Enable this mode for customer storefront access.</p>
                        </div>
                    </div>

                    <!-- Sort Order -->
                    <div>
                        <label for="sort_order" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Sort Order
                        </label>
                        <input id="sort_order" type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('sort_order') border-rose-500 @enderror">
                        <p class="text-[11px] text-slate-500 mt-1">Lower numbers appear first in the header switcher.</p>
                        @error('sort_order')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Icon Class -->
                    <div>
                        <label for="icon" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Icon Class <span class="text-slate-500 font-normal">(FontAwesome or custom)</span>
                        </label>
                        <input id="icon" type="text" name="icon" value="{{ old('icon') }}"
                            placeholder="e.g. fa-solid fa-bag-shopping"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('icon') border-rose-500 @enderror">
                        <p class="text-[11px] text-slate-500 mt-1">Examples: <span class="text-slate-400 font-mono">fa-solid fa-bag-shopping</span>, <span class="text-slate-400 font-mono">fa-solid fa-utensils</span>, <span class="text-slate-400 font-mono">fa-solid fa-bolt</span></p>
                        @error('icon')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Submit Actions Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-3">
                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-md shadow-indigo-600/30 transition cursor-pointer">
                        Create Mode
                    </button>
                    <a href="{{ route('admin.modes.index') }}" class="block text-center w-full py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-slate-200 text-xs font-medium transition">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        const unlockBtn = document.getElementById('unlockSlugBtn');
        let autoSlug = true;

        if (slugInput.value.trim() !== '') {
            autoSlug = false;
        }

        nameInput.addEventListener('input', function () {
            if (autoSlug) {
                slugInput.value = generateSlug(this.value);
            }
        });

        slugInput.addEventListener('input', function () {
            autoSlug = false;
        });

        unlockBtn.addEventListener('click', function () {
            autoSlug = false;
            slugInput.focus();
        });

        function generateSlug(text) {
            return text.toString().toLowerCase().trim()
                .replace(/[\s\W-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    });
</script>
@endpush
@endsection

@extends('admin.layouts.admin')

@section('title', 'Site & System Settings')

@section('content')
<div class="space-y-8 max-w-3xl">
    <!-- Page Header -->
    <div class="pb-5 border-b border-slate-800 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Site &amp; System Settings</h1>
            <p class="text-xs text-slate-400 mt-1">Manage global brand identity, site logo, and storefront theme preferences.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
            <i class="fa-solid fa-sliders text-[11px]"></i> Global Config
        </span>
    </div>

    <!-- Main Settings Form -->
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Card 1: Brand Identity & Logo -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-store text-base"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">Brand Identity &amp; Logo</h2>
                    <p class="text-xs text-slate-400">Customizes the site name and official logo displayed across storefront and admin areas.</p>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Site Name Input -->
                <div>
                    <label for="site_name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Site / Brand Name
                    </label>
                    <div class="relative">
                        <input type="text"
                               id="site_name"
                               name="site_name"
                               value="{{ old('site_name', $siteName) }}"
                               placeholder="e.g. Shopy, MyStore 2026"
                               class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('site_name') border-rose-500 @enderror">
                    </div>
                    @error('site_name')
                        <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-slate-500 mt-1.5">
                        Default fallback is <strong>Shopy</strong> if left empty. Updates storefront header, footer, titles, and admin portal.
                    </p>
                </div>

                <!-- Site Logo -->
                <div class="space-y-4">
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                        Site Logo
                    </label>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-6 p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80">
                        <!-- Current Logo Preview -->
                        <div class="flex flex-col items-center gap-2 shrink-0">
                            <div class="w-32 h-16 rounded-xl bg-white/95 border border-slate-700 flex items-center justify-center p-2 shadow-sm overflow-hidden">
                                <img id="logoPreview"
                                     src="{{ $siteLogoUrl }}"
                                     alt="{{ $siteName }}"
                                     class="max-h-full max-w-full object-contain">
                            </div>
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $hasCustomLogo ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $hasCustomLogo ? 'bg-emerald-400' : 'bg-slate-400' }}"></span>
                                {{ $hasCustomLogo ? 'Custom Uploaded' : 'Default Logo' }}
                            </span>
                        </div>

                        <!-- Upload New Logo Controls -->
                        <div class="flex-1 space-y-3">
                            <div>
                                <input type="file"
                                       id="site_logo"
                                       name="site_logo"
                                       accept="image/png,image/jpeg,image/svg+xml,image/webp"
                                       class="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 file:cursor-pointer transition cursor-pointer @error('site_logo') border-rose-500 @enderror">
                                @error('site_logo')
                                    <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>
                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                Recommended dimensions: <strong>200 &times; 50 px</strong> (transparent PNG or SVG). Max size: 2MB.
                            </p>

                            @if($hasCustomLogo)
                                <div class="pt-2 border-t border-slate-800 flex items-center gap-2">
                                    <label class="inline-flex items-center gap-2 text-xs text-rose-400 hover:text-rose-300 cursor-pointer">
                                        <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-700 bg-slate-900 text-rose-600 focus:ring-rose-500">
                                        <span>Reset to Default System Logo</span>
                                    </label>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Storefront Theme Permissions -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-palette text-base"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">Storefront Theme Permissions</h2>
                    <p class="text-xs text-slate-400">Control whether customers on the storefront can switch to Dark Mode.</p>
                </div>
            </div>

            <div>
                <label for="is_dark_mode" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    Dark Theme Permission
                </label>
                <select id="is_dark_mode" name="is_dark_mode"
                        class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition cursor-pointer">
                    <option value="0" {{ !$isDarkMode ? 'selected' : '' }}>No &mdash; Only Light Theme</option>
                    <option value="1" {{ $isDarkMode ? 'selected' : '' }}>Yes &mdash; Enable Dark Theme (User Can Change Both)</option>
                </select>
                <p class="text-[11px] text-slate-500 mt-1.5">
                    When disabled, the customer storefront will force Light Mode and hide the theme toggle pill in the header.
                </p>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-8 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-xl shadow-indigo-600/30 transition transform active:scale-95 cursor-pointer">
                <i class="fa-solid fa-floppy-disk text-xs"></i>
                Save All Settings
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const logoInput = document.getElementById('site_logo');
    const logoPreview = document.getElementById('logoPreview');

    if (logoInput && logoPreview) {
        logoInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    logoPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
@endsection

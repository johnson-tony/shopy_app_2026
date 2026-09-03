@extends('admin.layouts.admin')

@section('title', 'System & Theme Settings')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-white">System &amp; Theme Settings</h1>
            <p class="text-xs text-slate-400 mt-1">Configure global appearance, light/dark modes, and platform metadata stored in the <code class="text-indigo-400 font-mono">admin_settings</code> table.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Theme & Appearance Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-xl">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-600/20 text-indigo-400">
                        <i class="fas fa-palette text-xs"></i>
                    </span>
                    Portal Appearance &amp; Theme
                </h2>
                <p class="text-xs text-slate-400 mt-1">Select the default theme for the administrator login screen and control panel. Changes take effect across the CRM portal immediately.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Light Theme Option -->
                <label class="relative flex flex-col p-5 rounded-2xl border-2 cursor-pointer transition {{ ($settings['theme'] ?? 'light') === 'light' ? 'border-indigo-500 bg-slate-800/60 ring-4 ring-indigo-500/10' : 'border-slate-800 bg-slate-950/40 hover:border-slate-700' }}">
                    <input type="radio" name="theme" value="light" class="sr-only" {{ ($settings['theme'] ?? 'light') === 'light' ? 'checked' : '' }}>
                    <div class="flex items-center justify-between mb-3">
                        <span class="flex items-center gap-2 font-bold text-sm text-white">
                            <span class="w-6 h-6 rounded-full bg-amber-400/20 text-amber-400 inline-flex items-center justify-center text-xs">
                                <i class="fas fa-sun"></i>
                            </span>
                            Light (White) Theme
                        </span>
                        <span class="w-4 h-4 rounded-full border-2 flex items-center justify-center {{ ($settings['theme'] ?? 'light') === 'light' ? 'border-indigo-500 bg-indigo-500' : 'border-slate-600' }}">
                            @if(($settings['theme'] ?? 'light') === 'light')
                                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                            @endif
                        </span>
                    </div>

                    <div class="h-20 w-full rounded-xl bg-slate-100 border border-slate-200 p-2.5 flex flex-col justify-between mb-3">
                        <div class="flex items-center justify-between">
                            <div class="w-12 h-2.5 bg-indigo-600 rounded"></div>
                            <div class="w-4 h-4 rounded-full bg-slate-300"></div>
                        </div>
                        <div class="space-y-1.5">
                            <div class="w-full h-3 bg-white rounded border border-slate-200"></div>
                            <div class="w-2/3 h-2 bg-slate-300 rounded"></div>
                        </div>
                    </div>

                    <p class="text-xs text-slate-400">Clean, high-contrast white layout with crisp blue/indigo typography, identical to modern SaaS portals.</p>
                </label>

                <!-- Dark Theme Option -->
                <label class="relative flex flex-col p-5 rounded-2xl border-2 cursor-pointer transition {{ ($settings['theme'] ?? 'light') === 'dark' ? 'border-indigo-500 bg-slate-800/60 ring-4 ring-indigo-500/10' : 'border-slate-800 bg-slate-950/40 hover:border-slate-700' }}">
                    <input type="radio" name="theme" value="dark" class="sr-only" {{ ($settings['theme'] ?? 'light') === 'dark' ? 'checked' : '' }}>
                    <div class="flex items-center justify-between mb-3">
                        <span class="flex items-center gap-2 font-bold text-sm text-white">
                            <span class="w-6 h-6 rounded-full bg-indigo-400/20 text-indigo-400 inline-flex items-center justify-center text-xs">
                                <i class="fas fa-moon"></i>
                            </span>
                            Dark (Midnight) Theme
                        </span>
                        <span class="w-4 h-4 rounded-full border-2 flex items-center justify-center {{ ($settings['theme'] ?? 'light') === 'dark' ? 'border-indigo-500 bg-indigo-500' : 'border-slate-600' }}">
                            @if(($settings['theme'] ?? 'light') === 'dark')
                                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                            @endif
                        </span>
                    </div>

                    <div class="h-20 w-full rounded-xl bg-slate-950 border border-slate-800 p-2.5 flex flex-col justify-between mb-3">
                        <div class="flex items-center justify-between">
                            <div class="w-12 h-2.5 bg-indigo-500 rounded"></div>
                            <div class="w-4 h-4 rounded-full bg-slate-800"></div>
                        </div>
                        <div class="space-y-1.5">
                            <div class="w-full h-3 bg-slate-900 rounded border border-slate-800"></div>
                            <div class="w-2/3 h-2 bg-slate-800 rounded"></div>
                        </div>
                    </div>

                    <p class="text-xs text-slate-400">Deep slate-950 midnight ambiance with sleek glowing borders, ideal for low-light environments.</p>
                </label>
            </div>
        </div>

        <!-- General Store Settings Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-xl">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-600/20 text-indigo-400">
                        <i class="fas fa-sliders text-xs"></i>
                    </span>
                    General Store &amp; Support Info
                </h2>
                <p class="text-xs text-slate-400 mt-1">Platform branding details stored in the database.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Site Name -->
                <div>
                    <label for="site_name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Site / Brand Name <span class="text-rose-400">*</span>
                    </label>
                    <input id="site_name" type="text" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}" required
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('site_name')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Support Email -->
                <div>
                    <label for="support_email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Support Email <span class="text-rose-400">*</span>
                    </label>
                    <input id="support_email" type="email" name="support_email" value="{{ old('support_email', $settings['support_email'] ?? '') }}" required
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('support_email')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Support Phone -->
                <div>
                    <label for="support_phone" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Support Phone
                    </label>
                    <input id="support_phone" type="text" name="support_phone" value="{{ old('support_phone', $settings['support_phone'] ?? '') }}"
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('support_phone')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-4 pt-4">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-lg shadow-indigo-600/30 transition">
                <i class="fas fa-check text-xs"></i>
                Save Settings
            </button>
        </div>
    </form>
</div>
@endsection

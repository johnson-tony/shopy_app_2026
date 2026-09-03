@extends('admin.layouts.admin')

@section('title', 'Theme Settings')

@section('content')
<div class="space-y-6 max-w-xl">
    <!-- Page Header -->
    <div class="pb-4 border-b border-slate-800">
        <h1 class="text-2xl font-black text-white">Theme Settings</h1>
    </div>

    <!-- Simple Dropdown Form -->
    <form method="POST" action="{{ route('admin.settings.update') }}" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-5 shadow-xl">
        @csrf
        @method('PUT')

        <div>
            <label for="is_dark_mode" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                Dark Theme Permission
            </label>
            <select id="is_dark_mode" name="is_dark_mode"
                    class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition cursor-pointer">
                <option value="0" {{ !$isDarkMode ? 'selected' : '' }}>No &mdash; Only Light Theme</option>
                <option value="1" {{ $isDarkMode ? 'selected' : '' }}>Yes &mdash; Enable Dark Theme (User Can Change Both)</option>
            </select>
        </div>

        <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-lg shadow-indigo-600/30 transition cursor-pointer">
            Save Settings
        </button>
    </form>
</div>
@endsection

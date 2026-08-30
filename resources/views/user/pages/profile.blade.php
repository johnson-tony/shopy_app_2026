@extends('user.layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Profile Settings</h1>
            <p class="text-sm text-slate-500">Manage your basic customer account information</p>
        </div>
        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
            &larr; Back to Dashboard
        </a>
    </div>

    <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-xs">
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Full Name
                </label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('name') border-rose-500 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Email Address
                </label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('email') border-rose-500 @enderror">
                @error('email')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone -->
            <div>
                <label for="phone" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Phone Number
                </label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('phone') border-rose-500 @enderror"
                    placeholder="+1 234 567 8900">
                @error('phone')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-6 border-t border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 mb-1">Change Password (Optional)</h3>
                <p class="text-xs text-slate-500 mb-4">Leave blank if you don't wish to change your password.</p>

                <div class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Current Password
                        </label>
                        <input id="current_password" type="password" name="current_password"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('current_password') border-rose-500 @enderror">
                        @error('current_password')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="new_password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                New Password
                            </label>
                            <input id="new_password" type="password" name="new_password"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('new_password') border-rose-500 @enderror"
                                placeholder="Min. 8 chars">
                            @error('new_password')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="new_password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                Confirm New Password
                            </label>
                            <input id="new_password_confirmation" type="password" name="new_password_confirmation"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                placeholder="Repeat new password">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('dashboard') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-white font-semibold bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 transition shadow-sm text-sm">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('user.layouts.app')

@section('title', 'Create Account')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Create Account</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Join Shopy 2026 today</p>
        </div>

        <form method="POST" action="{{ route('register.submit') }}" class="space-y-4">
            @csrf

            <!-- Full Name -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Full Name
                </label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('name') border-rose-500 @enderror"
                    placeholder="Jane Doe">
                @error('name')
                    <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Email Address
                </label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('email') border-rose-500 @enderror"
                    placeholder="jane@example.com">
                @error('email')
                    <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone Number -->
            <div>
                <label for="phone" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Phone Number
                </label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('phone') border-rose-500 @enderror"
                    placeholder="+1 234 567 8900">
                @error('phone')
                    <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password with Eye Toggle -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Password
                </label>
                <div class="relative">
                    <input id="password" type="password" name="password" required
                        class="w-full pl-3.5 pr-11 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('password') border-rose-500 @enderror"
                        placeholder="At least 8 characters">
                    <button type="button" id="toggleRegisterPasswordBtn"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 p-1 text-sm focus:outline-none cursor-pointer transition flex items-center justify-center"
                        title="Show/Hide Password" aria-label="Toggle password visibility">
                        <i class="fa-regular fa-eye" id="toggleRegisterPasswordIcon"></i>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Confirmation with Eye Toggle -->
            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Confirm Password
                </label>
                <div class="relative">
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                        class="w-full pl-3.5 pr-11 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                        placeholder="Repeat password">
                    <button type="button" id="toggleConfirmPasswordBtn"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 p-1 text-sm focus:outline-none cursor-pointer transition flex items-center justify-center"
                        title="Show/Hide Password" aria-label="Toggle confirm password visibility">
                        <i class="fa-regular fa-eye" id="toggleConfirmPasswordIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full mt-2 py-2.5 px-4 rounded-xl text-white font-semibold bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 focus:ring-4 focus:ring-indigo-200 dark:focus:ring-indigo-900 transition shadow-sm cursor-pointer">
                Create My Account
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-700 text-center text-xs text-slate-500 dark:text-slate-400">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">Sign in</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function setupToggle(buttonId, inputId, iconId) {
        const btn = document.getElementById(buttonId);
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (btn && input && icon) {
            btn.addEventListener('click', function () {
                const isPassword = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPassword ? 'text' : 'password');

                if (isPassword) {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        }
    }

    setupToggle('toggleRegisterPasswordBtn', 'password', 'toggleRegisterPasswordIcon');
    setupToggle('toggleConfirmPasswordBtn', 'password_confirmation', 'toggleConfirmPasswordIcon');
});
</script>
@endsection

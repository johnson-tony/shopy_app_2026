@extends('user.layouts.app')

@section('title', 'Customer Sign In')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Welcome Back</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Sign in to your customer account</p>
        </div>

        <a href="{{ route('google.redirect') }}"
            class="w-full flex items-center justify-center gap-3 py-2.5 px-4 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white font-semibold text-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:ring-4 focus:ring-slate-200 dark:focus:ring-slate-900 transition shadow-sm cursor-pointer">
            <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#4285F4" d="M21.35 12.27c0-.79-.07-1.55-.22-2.27H12v4.3h5.24a4.48 4.48 0 0 1-1.94 2.94v2.45h3.14c1.84-1.69 2.91-4.18 2.91-7.42z"/>
                <path fill="#34A853" d="M12 21.75c2.63 0 4.84-.87 6.45-2.36l-3.14-2.45c-.87.58-1.98.93-3.31.93-2.54 0-4.69-1.72-5.46-4.03H3.3v2.53A9.75 9.75 0 0 0 12 21.75z"/>
                <path fill="#FBBC05" d="M6.54 13.84A5.86 5.86 0 0 1 6.24 12c0-.64.11-1.26.3-1.84V7.63H3.3A9.75 9.75 0 0 0 2.25 12c0 1.57.38 3.05 1.05 4.37l3.24-2.53z"/>
                <path fill="#EA4335" d="M12 6.13c1.43 0 2.71.49 3.72 1.45l2.79-2.79C16.83 3.22 14.63 2.25 12 2.25A9.75 9.75 0 0 0 3.3 7.63l3.24 2.53C7.31 7.85 9.46 6.13 12 6.13z"/>
            </svg>
            Continue with Google
        </a>

        <div class="my-6 flex items-center gap-3">
            <div class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></div>
            <span class="text-xs font-medium uppercase tracking-wider text-slate-400">or</span>
            <div class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></div>
        </div>

        <form method="POST" action="{{ route('login.submit') }}" class="space-y-5">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Email Address
                </label>
                <input id="email" type="email" name="email" value="{{ old('email', request('email')) }}" required autofocus
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('email') border-rose-500 @enderror"
                    placeholder="you@example.com">
                @error('email')
                    <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password with Eye Toggle -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Password
                    </label>
                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition">
                        Forgot password?
                    </a>
                </div>
                <div class="relative">
                    <input id="password" type="password" name="password" required
                        class="w-full pl-3.5 pr-11 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('password') border-rose-500 @enderror"
                        placeholder="••••••••">
                    <button type="button" id="togglePasswordBtn"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 p-1 text-sm focus:outline-none cursor-pointer transition flex items-center justify-center"
                        title="Show/Hide Password" aria-label="Toggle password visibility">
                        <i class="fa-regular fa-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-indigo-600 border-slate-300 dark:border-slate-600 focus:ring-indigo-500">
                    <span>Remember me</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-white font-semibold bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 focus:ring-4 focus:ring-indigo-200 dark:focus:ring-indigo-900 transition shadow-sm cursor-pointer">
                Sign In to Account
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-700 text-center text-xs text-slate-500 dark:text-slate-400">
            Don't have an account yet?
            <a href="{{ route('register') }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">Create one now</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('password');
    const togglePasswordBtn = document.getElementById('togglePasswordBtn');
    const togglePasswordIcon = document.getElementById('togglePasswordIcon');

    if (togglePasswordBtn && passwordInput && togglePasswordIcon) {
        togglePasswordBtn.addEventListener('click', function () {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

            if (isPassword) {
                togglePasswordIcon.classList.remove('fa-eye');
                togglePasswordIcon.classList.add('fa-eye-slash');
            } else {
                togglePasswordIcon.classList.remove('fa-eye-slash');
                togglePasswordIcon.classList.add('fa-eye');
            }
        });
    }
});
</script>
@endsection

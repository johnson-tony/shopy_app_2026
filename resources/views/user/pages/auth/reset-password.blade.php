@extends('user.layouts.app')

@section('title', 'Set New Password')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
        <!-- Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 mb-3 shadow-xs">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Set New Password</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Your reset code has been verified. Choose your new password.
            </p>
        </div>

        <!-- Verified badge -->
        <div class="mb-6 p-3 bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 rounded-xl text-xs text-emerald-800 dark:text-emerald-300 flex items-center justify-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>Verified for: <strong class="font-semibold">{{ $email ?? 'Your account' }}</strong></span>
        </div>

        <!-- Password Reset Form -->
        <form id="resetForm" method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf

            @if(!empty($token))
                <input type="hidden" name="token" value="{{ $token }}">
            @endif

            @if(!empty($email))
                <input type="hidden" name="email" value="{{ $email }}">
            @endif

            @if(!empty($otp))
                <input type="hidden" name="otp" value="{{ $otp }}">
            @endif

            <!-- New Password with Eye Toggle -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    New Password
                </label>
                <div class="relative">
                    <input id="password" type="password" name="password" required autofocus
                        class="w-full pl-3.5 pr-11 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('password') border-rose-500 @enderror"
                        placeholder="At least 8 characters">
                    <button type="button" id="toggleResetPasswordBtn"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 p-1 text-sm focus:outline-none cursor-pointer transition flex items-center justify-center"
                        title="Show/Hide Password" aria-label="Toggle password visibility">
                        <i class="fa-regular fa-eye" id="toggleResetPasswordIcon"></i>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password with Eye Toggle -->
            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Confirm New Password
                </label>
                <div class="relative">
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                        class="w-full pl-3.5 pr-11 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                        placeholder="Repeat new password">
                    <button type="button" id="toggleConfirmResetPasswordBtn"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 p-1 text-sm focus:outline-none cursor-pointer transition flex items-center justify-center"
                        title="Show/Hide Password" aria-label="Toggle confirm password visibility">
                        <i class="fa-regular fa-eye" id="toggleConfirmResetPasswordIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full mt-2 py-2.5 px-4 rounded-xl text-white font-semibold bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 focus:ring-4 focus:ring-indigo-200 dark:focus:ring-indigo-900 transition shadow-sm cursor-pointer">
                Update Password &amp; Continue
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-700 text-center text-xs">
            <a href="{{ route('login') }}" class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition">
                &larr; Back to Sign In
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Password Eye Toggles
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

    setupToggle('toggleResetPasswordBtn', 'password', 'toggleResetPasswordIcon');
    setupToggle('toggleConfirmResetPasswordBtn', 'password_confirmation', 'toggleConfirmResetPasswordIcon');
});
</script>
@endsection

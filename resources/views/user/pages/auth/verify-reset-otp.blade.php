@extends('user.layouts.app')

@section('title', 'Verify Reset Code')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
        <!-- Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Verify Reset Code</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Enter the 6-digit code sent to your email to continue
            </p>
        </div>

        <!-- Cross-device helper badge -->
        <div class="mb-5 p-3 bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/60 rounded-xl text-xs text-indigo-900 dark:text-indigo-300 leading-relaxed">
            <div class="font-semibold flex items-center gap-1.5 mb-0.5 text-indigo-700 dark:text-indigo-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <span>Multi-Device Friendly</span>
            </div>
            <span>Check your email on phone or PC. Enter the 6-digit code below to unlock the new password form.</span>
        </div>

        <!-- OTP Verification Form -->
        <form id="verifyOtpForm" method="POST" action="{{ route('password.verify_otp') }}" class="space-y-5">
            @csrf

            <!-- Email Address -->
            @if(!empty($email))
                <input type="hidden" name="email" id="emailHidden" value="{{ $email }}">
                <div class="text-center pb-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200">
                        <span>Verifying for:</span>
                        <strong class="text-indigo-600 dark:text-indigo-400">{{ $email }}</strong>
                    </span>
                </div>
            @else
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        Your Registered Email
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('email') border-rose-500 @enderror"
                        placeholder="you@example.com">
                    @error('email')
                        <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <!-- 6-digit Reset OTP Code -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2 text-center">
                    6-Digit Reset Code
                </label>

                <!-- 6 Individual Digit Inputs -->
                <div class="flex justify-center gap-2 sm:gap-2.5" id="digitInputs">
                    @for ($i = 0; $i < 6; $i++)
                        <input type="text"
                               inputmode="numeric"
                               maxlength="1"
                               pattern="[0-9]*"
                               data-index="{{ $i }}"
                               value=""
                               class="otp-digit w-10 h-12 sm:w-11 sm:h-13 text-center text-xl font-bold rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-xs"
                               autocomplete="off">
                    @endfor
                </div>

                <!-- Hidden input synced for form submission -->
                <input type="hidden" id="otp" name="otp" value="{{ old('otp', '') }}">

                @error('otp')
                    <p class="mt-2 text-xs text-rose-600 dark:text-rose-400 text-center font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full py-2.5 px-4 rounded-xl text-white font-semibold bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 focus:ring-4 focus:ring-indigo-200 dark:focus:ring-indigo-900 transition shadow-sm cursor-pointer">
                Verify Code &amp; Proceed
            </button>
        </form>

        <!-- Resend OTP Form -->
        <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-700 text-center text-xs text-slate-500 dark:text-slate-400 space-y-2">
            <div>
                <span>Didn't receive the reset code?</span>
                <form method="POST" action="{{ route('password.resend') }}" class="inline-block ml-1">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email ?? old('email') }}">
                    <button type="submit" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 cursor-pointer">
                        Resend Code
                    </button>
                </form>
            </div>
            <div>
                <span>Entered wrong email?</span>
                <a href="{{ route('password.request', ['change_email' => 1]) }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 ml-1">
                    Change email address
                </a>
            </div>
        </div>

        <div class="mt-4 text-center text-xs">
            <a href="{{ route('login') }}" class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition">
                &larr; Back to Sign In
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const digitInputs = document.querySelectorAll('.otp-digit');
    const realOtpInput = document.getElementById('otp');
    const form = document.getElementById('verifyOtpForm');

    function syncOtp() {
        let code = '';
        digitInputs.forEach(input => {
            code += input.value.trim();
        });
        realOtpInput.value = code;
    }

    if (digitInputs.length > 0) {
        if (!digitInputs[0].value) {
            digitInputs[0].focus();
        }

        digitInputs.forEach((input, idx) => {
            input.addEventListener('input', function (e) {
                const val = this.value.replace(/[^0-9]/g, '');
                this.value = val ? val[val.length - 1] : '';
                syncOtp();

                if (this.value && idx < digitInputs.length - 1) {
                    digitInputs[idx + 1].focus();
                }
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !this.value && idx > 0) {
                    digitInputs[idx - 1].focus();
                }
            });

            input.addEventListener('paste', function (e) {
                e.preventDefault();
                const pasteData = (e.clipboardData || window.clipboardData).getData('text');
                const cleanDigits = pasteData.replace(/[^0-9]/g, '').slice(0, 6);

                if (cleanDigits.length > 0) {
                    cleanDigits.split('').forEach((char, i) => {
                        if (digitInputs[i]) {
                            digitInputs[i].value = char;
                        }
                    });
                    syncOtp();
                    const nextIdx = Math.min(cleanDigits.length, digitInputs.length - 1);
                    digitInputs[nextIdx].focus();
                }
            });
        });
    }

    form.addEventListener('submit', function () {
        syncOtp();
    });
});
</script>
@endsection

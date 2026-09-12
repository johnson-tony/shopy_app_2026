<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Delivery Partner Login | {{ \App\Models\AdminSetting::siteName() }} Delivery</title>

    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background: radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.15) 0px, transparent 50%),
                        radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.15) 0px, transparent 50%),
                        #020617;
        }
    </style>
</head>
<body class="min-h-full flex flex-col items-center justify-center p-4 font-sans antialiased relative">
    <div class="w-full max-w-md my-8">
        <!-- Logo & Partner Portal Branding -->
        <div class="text-center mb-8">
            @if(\App\Models\AdminSetting::hasCustomLogo())
                <img src="{{ \App\Models\AdminSetting::siteLogoUrl() }}" alt="{{ \App\Models\AdminSetting::siteName() }}" class="h-12 w-auto mx-auto object-contain mb-4">
            @else
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-600 text-white font-black text-2xl shadow-xl shadow-emerald-600/30 mb-4 ring-4 ring-emerald-500/20">
                    <i class="fa-solid fa-bolt text-base"></i>
                </div>
            @endif
            <h1 class="text-2xl font-black tracking-tight text-white transition-colors">
                Delivery Partner Portal
            </h1>
            <p class="text-xs text-slate-400 mt-1 uppercase tracking-widest font-mono">
                {{ strtoupper(\App\Models\AdminSetting::siteName()) }} ON-DUTY LOGIN
            </p>
        </div>

        <!-- Login Container Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 space-y-6 shadow-2xl">
            @include('partner.components.alert')

            <form method="POST" action="{{ route('partner.login.submit') }}" class="space-y-5">
                @csrf

                <!-- Partner Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Partner Email
                    </label>
                    <div class="relative">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition @error('email') border-rose-500 @enderror"
                            placeholder="rider@shopy.test">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Partner Password -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <input id="password" type="password" name="password" required
                            class="w-full pl-4 pr-12 py-3 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition @error('password') border-rose-500 @enderror"
                            placeholder="••••••••">
                        <button type="button" id="togglePasswordBtn"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-emerald-500 p-1 text-sm focus:outline-none cursor-pointer transition"
                                title="Show/Hide Password">
                            <i class="fa-regular fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-600 text-emerald-500 focus:ring-emerald-500">
                        <span>Keep me signed in</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3 px-4 rounded-xl text-white font-bold bg-emerald-600 hover:bg-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition shadow-lg shadow-emerald-600/30 text-sm cursor-pointer">
                    <i class="fa-solid fa-right-to-bracket text-xs mr-1"></i>
                    Go On-Duty
                </button>
            </form>

            <div class="pt-4 border-t border-slate-800 text-center text-xs text-slate-400 space-y-3">
                <p>
                    Want to deliver with us?
                    <a href="{{ route('partner.register') }}" class="text-emerald-400 hover:text-emerald-300 font-bold transition ml-1">
                        Apply &amp; Register Now &rarr;
                    </a>
                </p>
                <div class="flex items-center justify-between text-slate-500 pt-2 border-t border-slate-800/60">
                    <span>Operational staff?</span>
                    <a href="{{ route('admin.login') }}" class="text-slate-400 hover:text-white font-semibold transition">
                        Admin Portal &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Toggle Script -->
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

    <!-- jQuery & Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    @include('components.toastr')
</body>
</html>
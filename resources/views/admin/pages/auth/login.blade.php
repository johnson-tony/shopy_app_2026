<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" data-theme="{{ $theme ?? 'light' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Administrator Login | Shopy 2026 Admin</title>

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
        :root[data-theme="light"] {
            --admin-bg: #f1f5f9;
            --admin-bg-gradient: radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.08) 0px, transparent 50%), radial-gradient(at 100% 100%, rgba(41, 98, 255, 0.08) 0px, transparent 50%), #f8fafc;
            --admin-card-bg: #ffffff;
            --admin-card-border: #e2e8f0;
            --admin-card-shadow: 0 20px 45px -12px rgba(41, 98, 255, 0.12), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --admin-text-title: #0f172a;
            --admin-text-sub: #64748b;
            --admin-text-label: #334155;
            --admin-input-bg: #ffffff;
            --admin-input-border: #cbd5e1;
            --admin-input-text: #0f172a;
            --admin-input-ph: #94a3b8;
            --admin-footer-border: #f1f5f9;
            --admin-toggle-bg: #ffffff;
            --admin-toggle-border: #cbd5e1;
            --admin-toggle-text: #0f172a;
        }

        :root[data-theme="dark"] {
            --admin-bg: #020617;
            --admin-bg-gradient: radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.15) 0px, transparent 50%), radial-gradient(at 100% 100%, rgba(41, 98, 255, 0.15) 0px, transparent 50%), #020617;
            --admin-card-bg: #0f172a;
            --admin-card-border: #1e293b;
            --admin-card-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            --admin-text-title: #ffffff;
            --admin-text-sub: #94a3b8;
            --admin-text-label: #cbd5e1;
            --admin-input-bg: #020617;
            --admin-input-border: #334155;
            --admin-input-text: #f8fafc;
            --admin-input-ph: #64748b;
            --admin-footer-border: #1e293b;
            --admin-toggle-bg: #0f172a;
            --admin-toggle-border: #334155;
            --admin-toggle-text: #f8fafc;
        }

        body {
            background: var(--admin-bg-gradient);
            transition: background 0.3s ease, color 0.3s ease;
        }

        .theme-card {
            background-color: var(--admin-card-bg);
            border-color: var(--admin-card-border);
            box-shadow: var(--admin-card-shadow);
            transition: all 0.3s ease;
        }

        .theme-title { color: var(--admin-text-title); }
        .theme-sub { color: var(--admin-text-sub); }
        .theme-label { color: var(--admin-text-label); }

        .theme-input {
            background-color: var(--admin-input-bg);
            border-color: var(--admin-input-border);
            color: var(--admin-input-text);
            transition: border-color 0.2s ease, background 0.2s ease;
        }

        .theme-input::placeholder {
            color: var(--admin-input-ph);
        }

        .theme-footer-border {
            border-color: var(--admin-footer-border);
        }
    </style>
</head>
<body class="min-h-full flex flex-col items-center justify-center p-4 font-sans antialiased relative">
    <div class="w-full max-w-md my-8">
        <!-- Logo & Admin Portal Branding -->
        <div class="text-center mb-8">
            @if(\App\Models\AdminSetting::hasCustomLogo())
                <img src="{{ \App\Models\AdminSetting::siteLogoUrl() }}" alt="{{ \App\Models\AdminSetting::siteName() }}" class="h-12 w-auto mx-auto object-contain mb-4">
            @else
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-600 text-white font-black text-2xl shadow-xl shadow-indigo-600/30 mb-4 ring-4 ring-indigo-500/20">
                    {{ strtoupper(substr(\App\Models\AdminSetting::siteName(), 0, 1)) }}
                </div>
            @endif
            <h1 class="text-2xl font-black tracking-tight theme-title transition-colors">
                {{ \App\Models\AdminSetting::siteName() }} Administrator Portal
            </h1>
            <p class="text-xs theme-sub mt-1 uppercase tracking-widest font-mono">
                {{ strtoupper(\App\Models\AdminSetting::siteName()) }} CONTROL PANEL
            </p>
        </div>

        <!-- Login Container Card -->
        <div class="theme-card border rounded-3xl p-8 space-y-6">
            @include('admin.components.alert')

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-5">
                @csrf

                <!-- Admin Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold theme-label uppercase tracking-wider mb-1.5">
                        Admin Email
                    </label>
                    <div class="relative">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="theme-input w-full px-4 py-3 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('email') border-rose-500 @enderror"
                            placeholder="admin@shopy.test">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Admin Password with Show/Hide Eye Toggle -->
                <div>
                    <label for="password" class="block text-xs font-semibold theme-label uppercase tracking-wider mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <input id="password" type="password" name="password" required
                            class="theme-input w-full pl-4 pr-12 py-3 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('password') border-rose-500 @enderror"
                            placeholder="••••••••">
                        <button type="button" id="togglePasswordBtn"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-500 p-1 text-sm focus:outline-none cursor-pointer transition"
                                title="Show/Hide Password">
                            <i class="fa-regular fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-xs theme-sub cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span>Remember admin session</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3 px-4 rounded-xl text-white font-bold bg-indigo-600 hover:bg-indigo-500 focus:ring-4 focus:ring-indigo-500/20 transition shadow-lg shadow-indigo-600/30 text-sm cursor-pointer">
                    Authenticate &amp; Access Dashboard
                </button>
            </form>

            <div class="pt-4 border-t theme-footer-border text-center text-xs theme-sub flex items-center justify-between">
                <span>Customer store?</span>
                <a href="{{ route('home') }}" class="text-indigo-600 hover:text-indigo-500 font-semibold transition">
                    &larr; Return to Storefront
                </a>
            </div>
        </div>
    </div>

    <!-- Theme & Password Toggle Scripts -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // ------------------------------------------------------------------
        // 1. Theme Management (Sync with admin_settings & LocalStorage)
        // ------------------------------------------------------------------
        const html = document.documentElement;
        const savedTheme = localStorage.getItem('admin_theme') || html.getAttribute('data-theme') || 'light';
        html.setAttribute('data-theme', savedTheme);

        // ------------------------------------------------------------------
        // 2. Password Toggle (Show / Hide)
        // ------------------------------------------------------------------
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

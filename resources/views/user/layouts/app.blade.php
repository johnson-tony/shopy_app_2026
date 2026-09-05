<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Welcome') | Shopy 2026</title>

    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">

    <!-- Inline Theme Synchronization Script: When Admin enables dark theme, users can toggle both themes freely -->
    <script>
        (function () {
            const darkAllowed = {{ ($isDarkMode ?? false) ? 'true' : 'false' }};
            let effectiveTheme = 'light';

            if (darkAllowed) {
                // Dark theme approved by admin: check user's saved preference; default to dark if not set yet
                const userTheme = localStorage.getItem('user_theme');
                if (userTheme === 'light') {
                    effectiveTheme = 'light';
                } else if (userTheme === 'dark') {
                    effectiveTheme = 'dark';
                } else {
                    effectiveTheme = 'dark'; // Admin enabled Dark Theme, so open with dark theme active
                }
            } else {
                // Dark theme NOT approved by admin: strictly Light theme only, user cannot change
                effectiveTheme = 'light';
                try {
                    localStorage.removeItem('user_theme');
                } catch (e) {}
            }

            document.documentElement.setAttribute('data-theme', effectiveTheme);
            if (effectiveTheme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            document.addEventListener('DOMContentLoaded', function() {
                if (document.body) {
                    document.body.setAttribute('data-theme', effectiveTheme);
                    if (effectiveTheme === 'dark') {
                        document.body.classList.add('dark');
                    } else {
                        document.body.classList.remove('dark');
                    }
                }
            });
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- Theme Styles (Header & Footer) -->
    <link rel="stylesheet" href="{{ asset('css/user-theme.css') }}">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-full flex flex-col font-sans antialiased text-slate-900 bg-slate-50 transition-colors">
    @php
        $isAuthPage = request()->routeIs('login', 'register', 'verification.*', 'password.*');
    @endphp

    {{-- Impersonation banner --}}
    @if (session()->get('impersonating'))
        @include('user.components.impersonation-banner')
    @endif

    @if (! $isAuthPage)
        <!-- Main Customer Header -->
        @include('user.components.header')
    @else
        <!-- Minimal Auth Header (Branding, Theme Toggle & Quick Return) -->
        <header class="py-6 px-4">
            <div class="max-w-md mx-auto flex items-center justify-between">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 font-bold text-xl tracking-tight text-slate-900 dark:text-white transition hover:opacity-90">
                    <span class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-md shadow-indigo-500/20">
                        <i class="fa-solid fa-bag-shopping text-sm"></i>
                    </span>
                    <span class="font-black text-lg tracking-wider">SHOPY<span class="text-indigo-600 dark:text-indigo-400">2026</span></span>
                </a>
                
                <div class="flex items-center gap-3">
                    @if(!empty($isDarkMode))
                        <button type="button"
                                class="theme-toggle-btn w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer"
                                id="userThemeToggle"
                                title="Toggle Light / Dark Theme"
                                aria-label="Toggle Theme">
                            <span id="userThemeIcon">
                                <i class="fas fa-moon"></i>
                            </span>
                        </button>
                    @endif

                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span>Store</span>
                    </a>
                </div>
            </div>
        </header>

        @if(!empty($isDarkMode))
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                const btn = document.getElementById('userThemeToggle');
                const icon = document.getElementById('userThemeIcon');
                if (!btn) return;

                function syncIcon(theme) {
                    if (icon) {
                        icon.innerHTML = theme === 'dark' ? '<i class="fas fa-sun text-amber-400"></i>' : '<i class="fas fa-moon text-slate-600"></i>';
                    }
                }

                syncIcon(document.documentElement.getAttribute('data-theme') || 'light');

                btn.addEventListener('click', function () {
                    const current = document.documentElement.getAttribute('data-theme') || 'light';
                    const next = current === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-theme', next);
                    if (document.body) document.body.setAttribute('data-theme', next);
                    if (next === 'dark') {
                        document.documentElement.classList.add('dark');
                        if (document.body) document.body.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        if (document.body) document.body.classList.remove('dark');
                    }
                    try { localStorage.setItem('user_theme', next); } catch (e) {}
                    syncIcon(next);
                });
            });
            </script>
        @endif
    @endif

    <!-- Main Content Area -->
    <main class="flex-1 {{ $isAuthPage ? 'pb-12' : 'py-8' }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    @if (! $isAuthPage)
        <!-- Main Customer Footer -->
        @include('user.components.footer')
    @endif

    <!-- jQuery & Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    @include('components.toastr')
    @stack('scripts')
</body>
</html>

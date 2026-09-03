<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Welcome') | Shopy 2026</title>

    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">

    <!-- Inline Theme Synchronization Script: Default is ALWAYS Light; user can toggle Dark only when admin approved -->
    <script>
        (function () {
            const darkAllowed = {{ ($isDarkMode ?? false) ? 'true' : 'false' }};
            let effectiveTheme = 'light';

            if (darkAllowed) {
                // Dark theme approved by admin: default is Light, but user can change/toggle to Dark
                const userTheme = localStorage.getItem('user_theme');
                effectiveTheme = (userTheme === 'dark') ? 'dark' : 'light';
            } else {
                // Dark theme NOT approved: strictly Light theme only, user cannot change
                effectiveTheme = 'light';
                localStorage.removeItem('user_theme');
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
    {{-- Impersonation banner --}}
    @if (session()->get('impersonating'))
        @include('user.components.impersonation-banner')
    @endif

    <!-- Main Customer Header -->
    @include('user.components.header')

    <!-- Main Content Area -->
    <main class="flex-1 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('user.components.alert')
            @yield('content')
        </div>
    </main>

    <!-- Main Customer Footer -->
    @include('user.components.footer')

    <!-- jQuery & Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    @include('components.toastr')
    @stack('scripts')
</body>
</html>

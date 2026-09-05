<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ \App\Models\AdminSetting::currentTheme() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') | Shopy 2026 Admin</title>

    <!-- Immediate Theme Application to Prevent Flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('admin_theme') || '{{ \App\Models\AdminSetting::currentTheme() }}';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Light Theme Overrides for Admin Portal */
        html[data-theme="light"],
        html[data-theme="light"] body {
            background-color: #f8fafc !important;
            color: #0f172a !important;
        }

        html[data-theme="light"] main {
            background-color: #f8fafc !important;
        }

        /* Sidebar & Header */
        html[data-theme="light"] aside,
        html[data-theme="light"] aside > div {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
            color: #334155 !important;
        }

        html[data-theme="light"] header {
            background-color: rgba(255, 255, 255, 0.95) !important;
            border-color: #e2e8f0 !important;
            color: #334155 !important;
        }

        /* Cards & Panels */
        html[data-theme="light"] .bg-slate-900 {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
        }

        html[data-theme="light"] .bg-slate-950 {
            background-color: #f1f5f9 !important;
            border-color: #e2e8f0 !important;
        }

        html[data-theme="light"] .bg-slate-950\/60,
        html[data-theme="light"] .bg-slate-950\/80 {
            background-color: #f1f5f9 !important;
        }

        html[data-theme="light"] .bg-slate-900\/95 {
            background-color: rgba(255, 255, 255, 0.95) !important;
            border-color: #e2e8f0 !important;
        }

        /* Borders */
        html[data-theme="light"] .border-slate-800,
        html[data-theme="light"] .border-slate-700 {
            border-color: #e2e8f0 !important;
        }

        /* Text Colors */
        html[data-theme="light"] .text-white,
        html[data-theme="light"] .text-slate-100 {
            color: #0f172a !important;
        }

        html[data-theme="light"] .text-slate-200 {
            color: #1e293b !important;
        }

        html[data-theme="light"] .text-slate-300 {
            color: #334155 !important;
        }

        html[data-theme="light"] .text-slate-400 {
            color: #64748b !important;
        }

        /* Hover States */
        html[data-theme="light"] .hover\:bg-slate-800\/60:hover,
        html[data-theme="light"] .hover\:bg-slate-800\/80:hover,
        html[data-theme="light"] .hover\:bg-slate-800:hover {
            background-color: #f1f5f9 !important;
        }

        html[data-theme="light"] .hover\:text-white:hover {
            color: #0f172a !important;
        }

        /* Form Controls */
        html[data-theme="light"] input,
        html[data-theme="light"] select,
        html[data-theme="light"] textarea {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }

        html[data-theme="light"] input::placeholder,
        html[data-theme="light"] textarea::placeholder {
            color: #94a3b8 !important;
        }

        /* Table rows & Dividers */
        html[data-theme="light"] tr.hover\:bg-slate-800\/40:hover,
        html[data-theme="light"] tr.hover\:bg-slate-800\/50:hover {
            background-color: #f8fafc !important;
        }

        html[data-theme="light"] .divide-slate-800 > :not([hidden]) ~ :not([hidden]) {
            border-color: #e2e8f0 !important;
        }

        /* Admin Tables: Full width and no text wrapping */
        .overflow-x-auto {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        .overflow-x-auto table,
        table {
            width: 100% !important;
            min-width: 100% !important;
            white-space: nowrap !important;
        }

        table th,
        table td {
            white-space: nowrap !important;
            vertical-align: middle;
        }
    </style>
</head>
<body class="min-h-screen flex bg-slate-950 text-slate-100 font-sans antialiased">
    <!-- Sticky Admin Sidebar -->
    @include('admin.components.sidebar')

    <!-- Right Side Content -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">
        <!-- Sticky Topbar -->
        @include('admin.components.topbar')

        <!-- Main Page Content -->
        <main class="flex-1 p-6 sm:p-8 bg-slate-950">
            <div class="max-w-7xl mx-auto space-y-6 pb-12">
                @include('admin.components.alert')
                @yield('content')
            </div>
        </main>
    </div>

    <!-- jQuery & Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    @include('components.toastr')

    <!-- Admin Theme Toggle Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const html = document.documentElement;
        const toggleBtn = document.getElementById('adminThemeToggle');
        const toggleIcon = document.getElementById('themeToggleIcon');
        const toggleText = document.getElementById('themeToggleText');

        function applyAdminTheme(theme) {
            html.setAttribute('data-theme', theme);
            localStorage.setItem('admin_theme', theme);

            if (toggleIcon) {
                if (theme === 'dark') {
                    toggleIcon.innerHTML = '<i class="fas fa-moon text-indigo-400 text-xs"></i>';
                    if (toggleText) toggleText.textContent = 'Dark';
                } else {
                    toggleIcon.innerHTML = '<i class="fas fa-sun text-amber-500 text-xs"></i>';
                    if (toggleText) toggleText.textContent = 'Light';
                }
            }
        }

        const current = html.getAttribute('data-theme') || localStorage.getItem('admin_theme') || 'dark';
        applyAdminTheme(current);

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                const cur = html.getAttribute('data-theme');
                const next = cur === 'dark' ? 'light' : 'dark';
                applyAdminTheme(next);
            });
        }
    });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') | Shopy 2026 Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex bg-slate-950 text-slate-100 font-sans antialiased">
    <!-- Admin Sidebar -->
    @include('admin.components.sidebar')

    <!-- Right Side Content -->
    <div class="flex-1 flex flex-col min-w-0">
        @include('admin.components.topbar')

        <main class="flex-1 p-8 overflow-y-auto bg-slate-950">
            <div class="max-w-7xl mx-auto space-y-6">
                @include('admin.components.alert')
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') | Shopy 2026 Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
</body>
</html>

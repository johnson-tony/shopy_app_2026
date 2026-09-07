<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Partner Panel') | {{ \App\Models\AdminSetting::siteName() }} Delivery</title>

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
        body {
            background-color: #0b1220;
            color: #e2e8f0;
        }

        .partner-card {
            background-color: #0f172a;
            border-color: #1e293b;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
        }
    </style>
</head>
<body class="min-h-screen font-sans antialiased">
    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-30 bg-slate-950/95 backdrop-blur border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-emerald-600 text-white font-black text-sm shadow-lg shadow-emerald-600/30">
                    <i class="fa-solid fa-bolt text-xs"></i>
                </span>
                <div class="flex flex-col leading-tight">
                    <span class="text-white font-bold text-sm tracking-wide">{{ \App\Models\AdminSetting::siteName() }} Delivery Partner</span>
                    <span class="text-[10px] text-emerald-400 font-mono uppercase tracking-widest">On-Duty Panel</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex flex-col items-end">
                    <span class="text-xs font-semibold text-white truncate max-w-[160px]">{{ $partner?->name }}</span>
                    <span class="text-[10px] text-slate-500">{{ $partner?->vehicle_type ? ucfirst($partner->vehicle_type) . ' Rider' : 'Rider' }}</span>
                </div>

                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold {{ $partner?->is_available ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $partner?->is_available ? 'bg-emerald-400 animate-pulse' : 'bg-slate-500' }}"></span>
                    {{ $partner?->is_available ? 'Online' : 'Offline' }}
                </span>

                <form method="POST" action="{{ route('partner.logout') }}">
                    @csrf
                    <button type="submit" title="Sign out of Partner Portal" class="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        @include('partner.components.alert')
        @yield('content')
    </main>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    @include('components.toastr')
    @stack('scripts')
</body>
</html>
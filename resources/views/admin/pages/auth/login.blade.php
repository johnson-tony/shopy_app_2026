<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Administrator Login | Shopy 2026 Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex items-center justify-center p-4 bg-slate-950 font-sans antialiased text-slate-100">
    <div class="w-full max-w-md">
        <!-- Logo & Admin Branding -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-600 text-white font-black text-2xl shadow-xl shadow-indigo-600/30 mb-4 ring-4 ring-indigo-500/20">
                A
            </div>
            <h1 class="text-2xl font-black tracking-tight text-white">Administrator Portal</h1>
            <p class="text-xs text-slate-400 mt-1 uppercase tracking-widest font-mono">SHOPY_APP_2026 CONTROL PANEL</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl shadow-black/60 space-y-6">
            @include('admin.components.alert')

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-5">
                @csrf

                <!-- Admin Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Admin Email
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('email') border-rose-500 @enderror"
                        placeholder="admin@shopy.test">
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Admin Password -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Password
                    </label>
                    <input id="password" type="password" name="password" required
                        class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('password') border-rose-500 @enderror"
                        placeholder="••••••••">
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                        <span>Remember admin session</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3 px-4 rounded-xl text-white font-bold bg-indigo-600 hover:bg-indigo-500 focus:ring-4 focus:ring-indigo-500/20 transition shadow-lg shadow-indigo-600/30 text-sm">
                    Authenticate & Access Dashboard
                </button>
            </form>

            <div class="pt-4 border-t border-slate-800/80 text-center text-xs text-slate-500 flex items-center justify-between">
                <span>Looking for store?</span>
                <a href="{{ route('home') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold transition">&larr; Return to Customer Store</a>
            </div>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Activate Administrator Account — Shopy</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 antialiased">
    <div class="w-full max-w-md">
        <!-- Logo Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600/10 border border-indigo-500/20 text-indigo-400 mb-4 shadow-lg shadow-indigo-500/10">
                <i class="fa-solid fa-shield-halved text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Activate Administrator Account</h1>
            <p class="text-slate-400 text-sm mt-1">Hello <span class="text-indigo-400 font-semibold">{{ $invitation->name }}</span>, choose a password to activate your account.</p>
        </div>

        <!-- Form Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
            @if ($errors->any())
                <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-xs mb-6">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.invitations.process', ['token' => $token]) }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Email Address</label>
                    <input type="email" value="{{ $invitation->email }}" disabled
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-400 text-sm opacity-70 cursor-not-allowed">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Create Password</label>
                    <input type="password" name="password" id="password" required placeholder="Minimum 8 characters"
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm placeholder-slate-500 focus:outline-none transition-all">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="Re-type password"
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm placeholder-slate-500 focus:outline-none transition-all">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-lock text-xs"></i> Activate &amp; Enter Admin Panel
                    </button>
                </div>
            </form>
        </div>

        <p class="text-center text-xs text-slate-600 mt-6">&copy; {{ date('Y') }} Shopy Platform. All rights reserved.</p>
    </div>
</body>
</html>

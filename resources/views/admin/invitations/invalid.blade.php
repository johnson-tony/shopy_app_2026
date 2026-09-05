<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invalid Invitation — Shopy</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 antialiased">
    <div class="w-full max-w-md text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 mb-4 shadow-lg shadow-amber-500/10">
            <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
        </div>
        
        <h1 class="text-2xl font-bold text-white tracking-tight">
            @if(!empty($isAccepted))
                Invitation Already Accepted
            @elseif(!empty($isExpired))
                Invitation Expired
            @else
                Invalid Invitation Link
            @endif
        </h1>

        <p class="text-slate-400 text-sm mt-2 mb-6">
            @if(!empty($isAccepted))
                This administrator invitation has already been claimed. You can log in using your established password.
            @elseif(!empty($isExpired))
                This invitation link has expired after 48 hours. Please request a Super Administrator to resend an invitation.
            @else
                The invitation token provided does not exist or has been revoked.
            @endif
        </p>

        <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-lg shadow-indigo-600/30 transition-all">
            <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i> Go to Admin Login
        </a>
    </div>
</body>
</html>

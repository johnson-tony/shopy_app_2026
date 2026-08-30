@extends('user.layouts.app')

@section('title', 'Customer Sign In')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Welcome Back</h1>
            <p class="text-sm text-slate-500 mt-1">Sign in to your customer account</p>
        </div>

        <form method="POST" action="{{ route('login.submit') }}" class="space-y-5">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Email Address
                </label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('email') border-rose-500 @enderror"
                    placeholder="you@example.com">
                @error('email')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Password
                    </label>
                </div>
                <input id="password" type="password" name="password" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('password') border-rose-500 @enderror"
                    placeholder="••••••••">
                @error('password')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                    <span>Remember me</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-white font-semibold bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 transition shadow-sm">
                Sign In to Account
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 text-center text-xs text-slate-500">
            Don't have an account yet?
            <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">Create one now</a>
        </div>

        <div class="mt-4 text-center">
            <span class="text-xs text-slate-400">Are you an administrator?</span>
            <a href="{{ route('admin.login') }}" class="text-xs font-medium text-slate-600 hover:text-slate-900 underline ml-1">Admin Portal</a>
        </div>
    </div>
</div>
@endsection

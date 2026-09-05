@extends('user.layouts.app')

@section('title', 'Customer Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header Banner -->
    <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-700 rounded-3xl p-8 text-white shadow-md">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-xs font-semibold backdrop-blur-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>Account: {{ ucfirst($user->status) }}</span>
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight">Customer Dashboard</h1>
                <p class="text-indigo-100 text-sm max-w-xl">
                    Welcome to your personal Shopy store account. Manage your profile and security from here.
                </p>
            </div>
            <div class="flex items-center gap-3 shrink-0 flex-wrap">
                <a href="{{ route('orders.index') }}" class="px-5 py-2.5 rounded-xl bg-white/20 text-white font-semibold text-sm hover:bg-white/30 transition shadow-xs backdrop-blur-xs flex items-center gap-2">
                    <i class="fa-solid fa-box text-xs"></i>
                    <span>My Orders</span>
                </a>
                <a href="{{ route('user.addresses.index') }}" class="px-5 py-2.5 rounded-xl bg-white/20 text-white font-semibold text-sm hover:bg-white/30 transition shadow-xs backdrop-blur-xs flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                    <span>My Addresses</span>
                </a>
                <a href="{{ route('profile') }}" class="px-5 py-2.5 rounded-xl bg-white text-indigo-700 font-semibold text-sm hover:bg-indigo-50 transition shadow-xs">
                    Edit Profile
                </a>
            </div>
        </div>
    </div>

    <!-- User Information Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Account Overview Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="font-bold text-slate-900 text-base">Account Details</h2>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold {{ $user->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    {{ ucfirst($user->status) }}
                </span>
            </div>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-xs text-slate-400 uppercase font-semibold block">Full Name</span>
                    <span class="font-medium text-slate-800">{{ $user->name }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 uppercase font-semibold block">Email Address</span>
                    <span class="font-medium text-slate-800">{{ $user->email }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 uppercase font-semibold block">Phone</span>
                    <span class="font-medium text-slate-800">{{ $user->phone ?? 'Not provided' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 uppercase font-semibold block">Member Since</span>
                    <span class="font-medium text-slate-800">{{ $user->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Storefront Access Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="font-bold text-slate-900 text-base">Store Profile</h2>
            </div>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-xs text-slate-400 uppercase font-semibold block mb-1">Account Type</span>
                    <span class="px-2.5 py-1 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-semibold">
                        Verified Store Customer
                    </span>
                </div>

                <div class="pt-2">
                    <span class="text-xs text-slate-400 uppercase font-semibold block mb-1">Security Status</span>
                    <p class="text-xs text-slate-600">
                        Isolated customer authentication session active.
                    </p>
                </div>
            </div>
        </div>

        <!-- Ready for Commerce Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="font-bold text-slate-900 text-base">Customer Security</h2>
            </div>
            <div class="space-y-2.5 text-xs text-slate-600">
                <div class="flex items-center gap-2 text-emerald-700 font-medium">
                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Customer Multi-Auth Verified</span>
                </div>
                <div class="flex items-center gap-2 text-emerald-700 font-medium">
                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Independent Customer Table</span>
                </div>
                <div class="flex items-center gap-2 text-emerald-700 font-medium">
                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Toastr Flash Notifications Enabled</span>
                </div>
                <p class="text-slate-400 text-[11px] pt-2">
                    E-commerce features (products, cart, orders) will connect directly to your customer account.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

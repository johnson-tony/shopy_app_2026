@extends('user.layouts.app')

@section('title', 'My Addresses')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 transition flex items-center gap-1">
                    &larr; Dashboard
                </a>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">My Addresses</h1>
            <p class="text-sm text-slate-500">Manage your delivery and billing addresses for quick checkout</p>
        </div>
        <div>
            <a href="{{ route('user.addresses.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white font-semibold bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 transition shadow-sm text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Add New Address</span>
            </a>
        </div>
    </div>

    @if($addresses->isEmpty())
        <!-- Empty State -->
        <div class="bg-white rounded-2xl p-12 border border-slate-200 shadow-xs text-center space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 mx-auto flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-lg font-bold text-slate-900">No addresses saved yet</h3>
                <p class="text-sm text-slate-500 max-w-md mx-auto">
                    Add a delivery address to make shopping and placing orders fast and seamless.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('user.addresses.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white font-semibold bg-indigo-600 hover:bg-indigo-700 transition shadow-sm text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Add Your First Address</span>
                </a>
            </div>
        </div>
    @else
        <!-- Address Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($addresses as $address)
                @include('user.components.address-card', ['address' => $address])
            @endforeach
        </div>
    @endif
</div>
@endsection

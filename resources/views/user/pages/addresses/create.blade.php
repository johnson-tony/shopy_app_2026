@extends('user.layouts.app')

@section('title', 'Add New Address')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('user.addresses.index') }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 transition flex items-center gap-1">
                    &larr; Back to Addresses
                </a>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">Add New Address</h1>
            <p class="text-sm text-slate-500">Provide your delivery location details</p>
        </div>
    </div>

    <!-- Add Address Form Card -->
    <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-xs">
        @include('user.components.address-form', [
            'action' => route('user.addresses.store'),
            'method' => 'POST',
            'address' => null,
            'user' => $user,
            'submitText' => 'Save Address',
        ])
    </div>
</div>
@endsection

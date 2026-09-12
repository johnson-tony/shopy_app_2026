@extends('user.layouts.app')

@section('title', 'Manage Addresses')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('profile') }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 transition flex items-center gap-1">
                    &larr; Back to Profile
                </a>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl font-bold text-slate-900">My Addresses</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                    {{ $addresses->count() }} {{ Str::plural('saved', $addresses->count()) }}
                </span>
            </div>
            <p class="text-sm text-slate-500">Manage your delivery and billing addresses</p>
        </div>

        <!-- Add Address Button (Toggle) -->
        <div>
            <button type="button" id="toggleAddAddressBtn" onclick="toggleAddAddressForm()"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white font-semibold bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 transition shadow-sm text-sm cursor-pointer">
                <svg id="addBtnIcon" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                </svg>
                <span id="addBtnText">Add A New Address</span>
            </button>
        </div>
    </div>

    <!-- Flipkart/Amazon Style Collapsible "ADD A NEW ADDRESS" Section -->
    <div id="addAddressCollapse" class="{{ $errors->any() && !old('_edit_id') ? '' : 'hidden' }} transition-all duration-300">
        <div class="bg-white rounded-2xl p-6 sm:p-8 border-2 border-indigo-500/30 shadow-md">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Add A New Delivery Address</h2>
                        <p class="text-xs text-slate-500">Enter your complete address details below</p>
                    </div>
                </div>
                <button type="button" onclick="toggleAddAddressForm()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            @include('user.components.address-form', [
                'action' => route('user.addresses.store'),
                'method' => 'POST',
                'address' => null,
                'user' => auth()->user(),
                'submitText' => 'Save Address',
            ])
        </div>
    </div>

    @if($addresses->isEmpty())
        <!-- Empty State (when user has 0 addresses) -->
        <div id="emptyAddressesCard" class="bg-white rounded-2xl p-12 border border-slate-200 shadow-xs text-center space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 mx-auto flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-lg font-bold text-slate-900">No addresses saved yet</h3>
                <p class="text-sm text-slate-500 max-w-md mx-auto">
                    Add a delivery address to easily place orders and speed up checkout.
                </p>
            </div>
        </div>
    @else
        <!-- Saved Addresses Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($addresses as $address)
                @include('user.components.address-card', ['address' => $address])
            @endforeach
        </div>
    @endif
</div>

<script>
    function toggleAddAddressForm(forceOpen = false) {
        const collapse = document.getElementById('addAddressCollapse');
        const emptyCard = document.getElementById('emptyAddressesCard');
        const btnText = document.getElementById('addBtnText');
        const btnIcon = document.getElementById('addBtnIcon');

        if (forceOpen || collapse.classList.contains('hidden')) {
            collapse.classList.remove('hidden');
            if (emptyCard) emptyCard.classList.add('hidden');
            btnText.innerText = 'Close Form';
            btnIcon.classList.add('rotate-45');
            collapse.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            collapse.classList.add('hidden');
            if (emptyCard) emptyCard.classList.remove('hidden');
            btnText.innerText = 'Add A New Address';
            btnIcon.classList.remove('rotate-45');
        }
    }
</script>
@endsection

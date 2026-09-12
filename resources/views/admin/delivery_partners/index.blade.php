@extends('admin.layouts.admin')

@section('title', 'Delivery Partner Fleet & KYC')

@section('content')
@php
    $adminUser = auth('admin')->user();
    $canCreate = $adminUser?->isSuperAdmin() || $adminUser?->hasPermission('partners.create') || $adminUser?->hasPermission('partners.edit') || $adminUser?->hasPermission('orders.edit');
    $canEdit = $adminUser?->isSuperAdmin() || $adminUser?->hasPermission('partners.edit') || $adminUser?->hasPermission('orders.edit');
    $canDelete = $adminUser?->isSuperAdmin() || $adminUser?->hasPermission('partners.delete');
@endphp
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-xs font-semibold text-indigo-400 mb-2">
                <i class="fa-solid fa-person-biking text-xs"></i>
                <span>Fleet Operations &amp; KYC Verification</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Delivery Partners</h1>
            <p class="text-slate-400 text-sm mt-1">
                Manage your delivery fleet, verify driver onboarding documents, approve KYC, and manage active riders.
            </p>
        </div>

        @if($canCreate)
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.delivery_partners.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition cursor-pointer shrink-0">
                <i class="fa-solid fa-user-plus"></i>
                <span>Add Delivery Partner</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Fleet</p>
            <p class="text-2xl font-black text-white mt-1.5">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-slate-900 border {{ $stats['pendingApproval'] > 0 ? 'border-amber-500/40 bg-amber-950/20' : 'border-slate-800' }} rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold {{ $stats['pendingApproval'] > 0 ? 'text-amber-400' : 'text-slate-400' }} uppercase tracking-wider">Pending KYC</p>
            <p class="text-2xl font-black {{ $stats['pendingApproval'] > 0 ? 'text-amber-300' : 'text-white' }} mt-1.5 flex items-center gap-2">
                @if($stats['pendingApproval'] > 0)
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                @endif
                <span>{{ number_format($stats['pendingApproval']) }}</span>
            </p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Online</p>
            <p class="text-2xl font-black text-emerald-400 mt-1.5 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>{{ number_format($stats['activeOnline']) }}</span>
            </p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Offline</p>
            <p class="text-2xl font-black text-slate-400 mt-1.5">{{ number_format($stats['activeOffline']) }}</p>
        </div>
    </div>

    <!-- Quick Status Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        <a href="{{ route('admin.delivery_partners.index', array_filter(['search' => $search ?: null, 'vehicle' => $vehicleFilter ?: null])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 cursor-pointer {{ $statusFilter === 'all' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' }}">
            <span>All Fleet</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $statusFilter === 'all' ? 'bg-white/20 text-white' : 'bg-slate-800 text-slate-400' }}">
                {{ $stats['total'] }}
            </span>
        </a>

        <a href="{{ route('admin.delivery_partners.index', array_filter(['status' => 'pending_approval', 'search' => $search ?: null, 'vehicle' => $vehicleFilter ?: null])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 cursor-pointer {{ $statusFilter === 'pending_approval' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' }}">
            <i class="fa-solid fa-id-card {{ $statusFilter === 'pending_approval' ? 'text-white' : 'text-amber-400' }}"></i>
            <span>Pending KYC Review</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $statusFilter === 'pending_approval' ? 'bg-white/20 text-white' : 'bg-amber-950/80 text-amber-400 border border-amber-800' }}">
                {{ $stats['pendingApproval'] }}
            </span>
        </a>

        <a href="{{ route('admin.delivery_partners.index', array_filter(['status' => 'active', 'search' => $search ?: null, 'vehicle' => $vehicleFilter ?: null])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 cursor-pointer {{ $statusFilter === 'active' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' }}">
            <i class="fa-solid fa-circle-check {{ $statusFilter === 'active' ? 'text-white' : 'text-emerald-400' }}"></i>
            <span>Approved &amp; Active</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $statusFilter === 'active' ? 'bg-white/20 text-white' : 'bg-slate-800 text-slate-400' }}">
                {{ $stats['active'] }}
            </span>
        </a>

        <a href="{{ route('admin.delivery_partners.index', array_filter(['status' => 'rejected', 'search' => $search ?: null, 'vehicle' => $vehicleFilter ?: null])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 cursor-pointer {{ $statusFilter === 'rejected' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/30' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' }}">
            <i class="fa-solid fa-ban {{ $statusFilter === 'rejected' ? 'text-white' : 'text-rose-400' }}"></i>
            <span>Rejected KYC</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $statusFilter === 'rejected' ? 'bg-white/20 text-white' : 'bg-slate-800 text-slate-400' }}">
                {{ $stats['rejected'] }}
            </span>
        </a>

        <a href="{{ route('admin.delivery_partners.index', array_filter(['status' => 'suspended', 'search' => $search ?: null, 'vehicle' => $vehicleFilter ?: null])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 cursor-pointer {{ $statusFilter === 'suspended' ? 'bg-slate-700 text-white shadow-md' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' }}">
            <i class="fa-solid fa-user-slash {{ $statusFilter === 'suspended' ? 'text-white' : 'text-slate-500' }}"></i>
            <span>Suspended</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $statusFilter === 'suspended' ? 'bg-white/20 text-white' : 'bg-slate-800 text-slate-400' }}">
                {{ $stats['suspended'] }}
            </span>
        </a>
    </div>

    <!-- Filters & Search Form -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-xl">
        <form method="GET" action="{{ route('admin.delivery_partners.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-center">
            @if($statusFilter !== 'all')
                <input type="hidden" name="status" value="{{ $statusFilter }}">
            @endif

            <div class="sm:col-span-2 relative">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Search by name, email, phone, vehicle plate, license #..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <div>
                <select name="vehicle" class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="">All Vehicles</option>
                    <option value="bike" {{ $vehicleFilter === 'bike' ? 'selected' : '' }}>Motorcycle / Bike</option>
                    <option value="scooter" {{ $vehicleFilter === 'scooter' ? 'selected' : '' }}>Scooter</option>
                    <option value="ev" {{ $vehicleFilter === 'ev' ? 'selected' : '' }}>Electric Vehicle (EV)</option>
                    <option value="bicycle" {{ $vehicleFilter === 'bicycle' ? 'selected' : '' }}>Bicycle</option>
                    <option value="van" {{ $vehicleFilter === 'van' ? 'selected' : '' }}>Delivery Van</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="w-full px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md shadow-indigo-600/30 transition cursor-pointer">
                    Filter
                </button>
                @if($search || $vehicleFilter || $statusFilter !== 'all')
                    <a href="{{ route('admin.delivery_partners.index') }}" class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition shrink-0" title="Clear Filters">
                        <i class="fa-solid fa-times"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Partners Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        @if($partners->isEmpty())
            <div class="text-center py-16 px-4">
                <div class="w-16 h-16 rounded-full bg-slate-800 text-slate-500 flex items-center justify-center mx-auto mb-4 text-xl">
                    <i class="fa-solid fa-person-biking"></i>
                </div>
                <h3 class="text-lg font-bold text-white mb-1">No delivery partners found</h3>
                <p class="text-slate-400 text-sm max-w-sm mx-auto">
                    @if($search || $statusFilter !== 'all' || $vehicleFilter)
                        Try clearing or modifying your search filters.
                    @else
                        Click "Add Delivery Partner" to onboard your first delivery driver.
                    @endif
                </p>
                <div class="pt-4">
                    <a href="{{ route('admin.delivery_partners.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Add First Delivery Partner</span>
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 bg-slate-950/50 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-4 px-6">Partner Details</th>
                            <th class="py-4 px-6">KYC Documents</th>
                            <th class="py-4 px-6">Vehicle &amp; Channels</th>
                            <th class="py-4 px-6">Status</th>
                            <th class="py-4 px-6">Duty Status</th>
                            <th class="py-4 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($partners as $partner)
                            @php
                                $badge = $partner->status_badge;
                            @endphp
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 font-black text-sm shrink-0">
                                            {{ strtoupper(substr($partner->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.delivery_partners.show', $partner) }}" class="font-bold text-white hover:text-indigo-400 transition block">
                                                {{ $partner->name }}
                                            </a>
                                            <p class="text-xs text-slate-400">{{ $partner->phone }}</p>
                                            <p class="text-[11px] text-slate-500">{{ $partner->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="space-y-1 text-xs">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 font-bold">DL</span>
                                            <span class="text-slate-300 font-mono text-[11px]">{{ $partner->license_number ?? 'Not uploaded' }}</span>
                                            @if($partner->license_image)
                                                <i class="fa-solid fa-file-image text-emerald-400 text-[11px]" title="License photo uploaded"></i>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-800 text-indigo-300 font-bold">{{ strtoupper($partner->id_proof_type ?? 'ID') }}</span>
                                            <span class="text-slate-400 font-mono text-[11px]">{{ $partner->id_proof_number ?? 'N/A' }}</span>
                                            @if($partner->id_proof_image)
                                                <i class="fa-solid fa-file-image text-emerald-400 text-[11px]" title="ID proof photo uploaded"></i>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5 text-xs text-slate-300">
                                            <i class="fa-solid fa-motorcycle text-indigo-400 text-xs"></i>
                                            <span class="font-semibold">{{ ucfirst($partner->vehicle_type ?? 'vehicle') }}</span>
                                            @if($partner->vehicle_number)
                                                <span class="text-slate-500 font-mono text-[11px]">({{ $partner->vehicle_number }})</span>
                                            @endif
                                        </div>
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @forelse($partner->modes as $mode)
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-800 text-slate-300 border border-slate-700">
                                                    {{ $mode->name }}
                                                </span>
                                            @empty
                                                <span class="text-[10px] text-slate-500 italic">All Channels</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $badge['bg'] }} {{ $badge['text'] }} border {{ $badge['border'] }}">
                                        <i class="{{ $badge['icon'] }}"></i>
                                        <span>{{ $badge['label'] }}</span>
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    @if($partner->status === \App\Models\DeliveryPartner::STATUS_ACTIVE)
                                        @if($partner->is_available)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                                                Online
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-800 text-slate-400 border border-slate-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                                Offline
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-500 italic">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($partner->isPendingApproval() && $canEdit)
                                            <form method="POST" action="{{ route('admin.delivery_partners.approve', $partner) }}">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow transition cursor-pointer" title="Quick Approve KYC">
                                                    <i class="fa-solid fa-check mr-1"></i> Approve
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('admin.delivery_partners.show', $partner) }}" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition" title="Inspect KYC Documents &amp; Activity">
                                            <i class="fa-solid fa-id-card text-xs"></i>
                                        </a>

                                        @if($canEdit)
                                            <a href="{{ route('admin.delivery_partners.edit', $partner) }}" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition" title="Edit Partner Details">
                                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                            </a>

                                            @if($partner->status !== \App\Models\DeliveryPartner::STATUS_PENDING_APPROVAL)
                                                <form method="POST" action="{{ route('admin.delivery_partners.toggle_status', $partner) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="p-2 rounded-lg {{ $partner->status === \App\Models\DeliveryPartner::STATUS_ACTIVE ? 'text-amber-400 hover:bg-amber-950/40' : 'text-emerald-400 hover:bg-emerald-950/40' }} transition cursor-pointer" title="{{ $partner->status === \App\Models\DeliveryPartner::STATUS_ACTIVE ? 'Suspend' : 'Reactivate' }}">
                                                        <i class="fa-solid {{ $partner->status === \App\Models\DeliveryPartner::STATUS_ACTIVE ? 'fa-user-slash' : 'fa-user-check' }} text-xs"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                        @if($canDelete)
                                            <form method="POST" action="{{ route('admin.delivery_partners.destroy', $partner) }}" onsubmit="return confirm('Permanently remove {{ $partner->name }} from fleet?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-950/40 transition cursor-pointer" title="Delete Partner">
                                                    <i class="fa-solid fa-trash text-xs"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($partners->hasPages())
                <div class="p-4 border-t border-slate-800">
                    {{ $partners->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection

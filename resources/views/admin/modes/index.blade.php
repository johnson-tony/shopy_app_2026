@extends('admin.layouts.admin')

@section('title', 'Mode Management')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-xs font-semibold text-indigo-400 mb-2">
                <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                <span>Multi-Service Architecture</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Shopping Mode Management</h1>
            <p class="text-slate-400 text-sm mt-1">
                Configure shopping and service modes (e.g. Shopy, Food, Minutes) for your customer storefront.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.modes.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md shadow-indigo-600/30 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add Mode</span>
            </a>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Total -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Modes</p>
            <p class="text-2xl font-black text-white mt-1.5">{{ $stats['total'] }}</p>
        </div>
        <!-- Active -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Modes</p>
            <p class="text-2xl font-black text-emerald-400 mt-1.5">{{ $stats['active'] }}</p>
        </div>
        <!-- Inactive -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Inactive Modes</p>
            <p class="text-2xl font-black text-slate-400 mt-1.5">{{ $stats['inactive'] }}</p>
        </div>
    </div>

    <!-- Filters & Search Form -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-xl">
        <form method="GET" action="{{ route('admin.modes.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-center">
            <!-- Search Keyword -->
            <div class="relative sm:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, slug or description..."
                    class="w-full px-4 py-2.5 pr-10 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Status Filter & Clear -->
            <div class="flex items-center gap-2">
                <select name="status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="">All Statuses</option>
                    <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                </select>

                <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition cursor-pointer">
                    Filter
                </button>

                @if ($search !== '' || $statusFilter !== null)
                    <a href="{{ route('admin.modes.index') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium transition" title="Clear Filters">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Modes Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full min-w-full text-left text-xs whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-3.5 px-4 w-12">ID</th>
                        <th class="py-3.5 px-4">Mode</th>
                        <th class="py-3.5 px-4">Slug</th>
                        <th class="py-3.5 px-4">Description</th>
                        <th class="py-3.5 px-4">Sort Order</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Created</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse ($modes as $mode)
                        <tr class="hover:bg-slate-800/30 transition">
                            <!-- ID -->
                            <td class="py-3.5 px-4 font-mono text-slate-500">
                                #{{ $mode->id }}
                            </td>

                            <!-- Icon & Name -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 shrink-0 overflow-hidden">
                                        @if ($mode->image_url)
                                            <img src="{{ $mode->image_url }}" alt="{{ $mode->name }}" class="w-full h-full object-contain p-1">
                                        @elseif ($mode->icon)
                                            <i class="{{ $mode->icon }} text-base text-indigo-400"></i>
                                        @else
                                            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-bold text-white text-sm">{{ $mode->name }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Slug -->
                            <td class="py-3.5 px-4 font-mono text-slate-400">
                                <span class="px-2 py-0.5 rounded bg-slate-950 border border-slate-800 text-[11px]">
                                    {{ $mode->slug }}
                                </span>
                            </td>

                            <!-- Description -->
                            <td class="py-3.5 px-4 text-slate-400 max-w-xs truncate">
                                {{ $mode->description ?? '—' }}
                            </td>

                            <!-- Sort Order -->
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-950 border border-slate-800 text-slate-300 font-mono text-xs">
                                    {{ $mode->sort_order }}
                                </span>
                            </td>

                            <!-- Active Status Toggle -->
                            <td class="py-3.5 px-4">
                                <form method="POST" action="{{ route('admin.modes.toggleStatus', $mode) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold transition cursor-pointer {{ $mode->status ? 'bg-emerald-950/70 text-emerald-300 border border-emerald-500/40 hover:bg-emerald-900/60' : 'bg-rose-950/70 text-rose-300 border border-rose-500/40 hover:bg-rose-900/60' }}" title="Click to toggle status">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $mode->status ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                        {{ $mode->status ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>

                            <!-- Created Date -->
                            <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                                {{ $mode->created_at ? $mode->created_at->format('M d, Y') : '—' }}
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.modes.edit', $mode) }}" class="p-2 rounded-lg bg-slate-800 hover:bg-indigo-600 text-slate-300 hover:text-white transition" title="Edit Mode">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    <form method="POST" action="{{ route('admin.modes.destroy', $mode) }}" onsubmit="return confirm('Are you sure you want to delete mode \'{{ $mode->name }}\'?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white transition cursor-pointer" title="Delete Mode">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-500 mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                    <p class="text-slate-300 font-semibold text-sm">No shopping modes found</p>
                                    <p class="text-slate-500 text-xs mt-1">Get started by creating your first service mode.</p>
                                    <a href="{{ route('admin.modes.create') }}" class="mt-4 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition">
                                        + Add Mode
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($modes->hasPages())
            <div class="pt-4 border-t border-slate-800">
                {{ $modes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

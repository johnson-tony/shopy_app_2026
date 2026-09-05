@extends('admin.layouts.admin')

@section('title', 'User Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-xl">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-xs font-semibold text-indigo-400 mb-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Storefront Customers</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">User Management</h1>
            <p class="text-slate-400 text-sm mt-1">
                View customer accounts and impersonate them to preview the storefront.
            </p>
        </div>
        <span class="px-3.5 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 font-mono text-xs">
            Total: {{ $users->total() }}
        </span>
    </div>

    <!-- Search -->
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2">
        <div class="relative flex-1 max-w-sm">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, email or phone..."
                class="w-full px-4 py-2.5 pr-10 rounded-xl bg-slate-900 border border-slate-800 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition">
            Search
        </button>
        @if ($search !== '')
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold transition">
                Clear
            </a>
        @endif
    </form>

    <!-- Users Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full min-w-full text-left text-xs whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">Contact</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Joined</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-xs font-bold text-indigo-400 shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="font-medium text-white">{{ $user->name }}</p>
                                        <p class="text-[11px] text-slate-500">#{{ $user->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <p class="font-mono text-slate-400">{{ $user->email }}</p>
                                <p class="text-[11px] text-slate-500">{{ $user->phone ?: 'No phone' }}</p>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $user->status === 'active' ? 'bg-emerald-950 text-emerald-400 border border-emerald-800/60' : 'bg-rose-950 text-rose-400 border border-rose-800/60' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $user->status === 'active' ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-400">{{ $user->created_at->format('Y-m-d') }}</td>
                            <td class="py-3 px-4 text-right">
                                @if ($user->isActive())
                                    <form method="POST" action="{{ route('admin.users.login-as', $user) }}" target="_blank" class="inline">
                                        @csrf
                                        <button type="submit" title="Login as {{ $user->name }}"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition shadow-sm cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 3h5m0 0v5m0-5L11 13m-5-6H4v14h14V9m-8 0h-2v2a3 3 0 006 0V7a3 3 0 00-3-3h0"/>
                                            </svg>
                                            Login as User
                                        </button>
                                    </form>
                                @else
                                    <span class="inline-block px-3 py-1.5 rounded-lg bg-slate-800 text-slate-500 text-xs font-semibold cursor-not-allowed" title="Cannot login as an inactive user">
                                        Unavailable
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center">
                                <p class="text-slate-500 text-sm">No users found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($users->hasPages())
            <div class="pt-2">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

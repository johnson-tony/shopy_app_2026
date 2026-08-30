@extends('admin.layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Header Hero Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-xl">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-xs font-semibold text-indigo-400 mb-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Role: {{ $admin->roles->pluck('name')->join(', ') }}</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Admin Control Center</h1>
            <p class="text-slate-400 text-sm mt-1">
                Centralized management for roles, permissions, users, and administrative security.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3.5 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 font-mono text-xs">
                Status: {{ strtoupper($admin->status) }}
            </span>
        </div>
    </div>

    <!-- Statistics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Users Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Users</span>
                <span class="p-2 rounded-xl bg-indigo-500/10 text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-black text-white">{{ $stats['total_users'] }}</span>
                <p class="text-xs text-slate-400 mt-1">Registered in database</p>
            </div>
        </div>

        <!-- Active Users Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Users</span>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-black text-white">{{ $stats['active_users'] }}</span>
                <p class="text-xs text-emerald-400/80 mt-1">Eligible to authenticate</p>
            </div>
        </div>

        <!-- System Roles Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Defined Roles</span>
                <span class="p-2 rounded-xl bg-amber-500/10 text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-black text-white">{{ $stats['total_roles'] }}</span>
                <p class="text-xs text-slate-400 mt-1">Super Admin, Admin, Manager, Customer</p>
            </div>
        </div>

        <!-- Permissions Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Permissions</span>
                <span class="p-2 rounded-xl bg-purple-500/10 text-purple-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-black text-white">{{ $stats['total_permissions'] }}</span>
                <p class="text-xs text-slate-400 mt-1">Granular permission nodes</p>
            </div>
        </div>
    </div>

    <!-- Users & Role Foundation Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div>
                <h2 class="text-base font-bold text-white">Seeded Foundation Accounts</h2>
                <p class="text-xs text-slate-400">Verified system test records</p>
            </div>
            <span class="text-xs font-mono text-indigo-400">Step 1 Ready</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Assigned Role</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @foreach ($recentUsers as $user)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4 font-medium text-white flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-[10px] font-bold">
                                    {{ substr($user->name, 0, 1) }}
                                </span>
                                <span>{{ $user->name }}</span>
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-400">{{ $user->email }}</td>
                            <td class="py-3 px-4">
                                @foreach ($user->roles as $role)
                                    <span class="inline-block px-2 py-0.5 rounded text-[11px] font-medium {{ $role->slug === 'super-admin' ? 'bg-purple-950 text-purple-300 border border-purple-800' : ($role->slug === 'admin' ? 'bg-indigo-950 text-indigo-300 border border-indigo-800' : 'bg-slate-800 text-slate-300 border border-slate-700') }}">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $user->status === 'active' ? 'bg-emerald-950 text-emerald-400 border border-emerald-800/60' : 'bg-rose-950 text-rose-400 border border-rose-800/60' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $user->status === 'active' ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right text-slate-400">{{ $user->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

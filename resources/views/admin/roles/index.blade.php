@extends('admin.layouts.admin')

@section('title', 'Roles & Permissions — Shopy Admin')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-xs font-semibold text-indigo-400 mb-2">
                <i class="fa-solid fa-shield-halved text-xs"></i>
                Access Control & Security
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Roles & Permissions</h1>
            <p class="text-slate-400 text-sm mt-1">Configure system roles, assign granular permissions, and scope shopping mode access.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold shadow-lg shadow-indigo-600/30 transition-all">
                <i class="fa-solid fa-plus text-xs"></i>
                Create New Role
            </a>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Roles</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                    <i class="fa-solid fa-shield-halved text-lg"></i>
                </div>
            </div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Active Roles</p>
                    <p class="text-2xl font-bold text-emerald-400 mt-1">{{ $stats['active'] }}</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                </div>
            </div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Permissions</p>
                    <p class="text-2xl font-bold text-cyan-400 mt-1">{{ $stats['permissions_count'] }}</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400">
                    <i class="fa-solid fa-key text-lg"></i>
                </div>
            </div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Shopping Modes</p>
                    <p class="text-2xl font-bold text-amber-400 mt-1">{{ $stats['modes_count'] }}</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                    <i class="fa-solid fa-layer-group text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-sm">
            <i class="fa-solid fa-circle-check text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-sm">
            <i class="fa-solid fa-circle-exclamation text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Roles Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="p-5 sm:p-6 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-white">System Roles</h2>
                <p class="text-xs text-slate-400 mt-0.5">Control permissions and mode assignments per role</p>
            </div>
            <form action="{{ route('admin.roles.index') }}" method="GET" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search roles..." class="bg-slate-950 border border-slate-800 text-white text-xs rounded-xl px-3.5 py-2 w-64 focus:outline-none focus:border-indigo-500">
                <button type="submit" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition-all">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-full text-left text-sm text-slate-300 whitespace-nowrap">
                <thead class="bg-slate-950/60 text-xs font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-4">Role Name & Slug</th>
                        <th class="px-6 py-4">Permissions</th>
                        <th class="px-6 py-4">Mode Access</th>
                        <th class="px-6 py-4">Staff Assigned</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($roles as $role)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-white">{{ $role->name }}</div>
                                <div class="flex items-center gap-2 mt-1 whitespace-nowrap">
                                    <code class="text-xs text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded border border-indigo-500/20 font-mono">{{ $role->slug }}</code>
                                    @if($role->slug === 'super-admin')
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded bg-amber-500/10 border border-amber-500/20 text-amber-400">
                                            <i class="fa-solid fa-crown text-[9px]"></i> ROOT
                                        </span>
                                    @endif
                                </div>
                                @if($role->description)
                                    <p class="text-xs text-slate-400 mt-1 whitespace-nowrap">{{ $role->description }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($role->slug === 'super-admin')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <i class="fa-solid fa-infinity text-[10px]"></i> Unrestricted Global
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                        <i class="fa-solid fa-key text-[10px]"></i> {{ $role->permissions->count() }} permissions
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($role->slug === 'super-admin')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <i class="fa-solid fa-globe text-[10px]"></i> All Modes
                                    </span>
                                @elseif($role->modes->isEmpty())
                                    <span class="text-xs text-slate-500 italic">No modes assigned</span>
                                @else
                                    <div class="flex flex-wrap gap-1.5 max-w-xs">
                                        @foreach($role->modes as $mode)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-slate-800 text-slate-300 border border-slate-700">
                                                @if($mode->icon)<i class="{{ $mode->icon }} text-[10px] text-indigo-400"></i>@endif
                                                {{ $mode->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="inline-flex items-center gap-1.5 text-xs text-slate-300 font-medium">
                                    <i class="fa-solid fa-users text-slate-500 text-xs"></i>
                                    {{ $role->admins_count }} admins
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($role->status)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-400 border border-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="p-2 rounded-xl bg-slate-800 hover:bg-indigo-600/30 text-slate-300 hover:text-indigo-400 transition-all text-xs" title="Edit Role & Permissions">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    @if($role->slug !== 'super-admin' && $role->slug !== 'admin')
                                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete role {{ $role->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded-xl bg-slate-800 hover:bg-rose-600/30 text-slate-300 hover:text-rose-400 transition-all text-xs" title="Delete Role">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <i class="fa-solid fa-shield-halved text-3xl mb-3 text-slate-600 block"></i>
                                No roles found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($roles->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                {{ $roles->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

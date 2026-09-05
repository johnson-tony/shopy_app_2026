@extends('admin.layouts.admin')

@section('title', 'Administrator Management — Shopy Admin')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-xs font-semibold text-indigo-400 mb-2">
                <i class="fa-solid fa-users-gear text-xs"></i>
                Administrative Staff &amp; RBAC
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Administrator Management</h1>
            <p class="text-slate-400 text-sm mt-1">Manage sub-administrators, dispatch secure invitations, and oversee role assignments.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.admins.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold shadow-lg shadow-indigo-600/30 transition-all">
                <i class="fa-solid fa-envelope-open-text text-xs"></i>
                Invite Sub-Admin
            </a>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Admins</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                    <i class="fa-solid fa-users text-lg"></i>
                </div>
            </div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Active Staff</p>
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
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Super Admins</p>
                    <p class="text-2xl font-bold text-amber-400 mt-1">{{ $stats['super_admins'] }}</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                    <i class="fa-solid fa-crown text-lg"></i>
                </div>
            </div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Pending Invites</p>
                    <p class="text-2xl font-bold text-cyan-400 mt-1">{{ $stats['pending_invitations'] }}</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400">
                    <i class="fa-solid fa-paper-plane text-lg"></i>
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

    <!-- Administrators Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="p-5 sm:p-6 border-b border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-white">Administrators</h2>
                <p class="text-xs text-slate-400 mt-0.5">Control administrative staff credentials, roles, and mode scopes</p>
            </div>

            <!-- Filters -->
            <form action="{{ route('admin.admins.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search name or email..."
                    class="bg-slate-950 border border-slate-800 text-white text-xs rounded-xl px-3.5 py-2 w-52 focus:outline-none focus:border-indigo-500">

                <select name="role" class="bg-slate-950 border border-slate-800 text-white text-xs rounded-xl px-3 py-2 focus:outline-none focus:border-indigo-500">
                    <option value="">All Roles</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}" {{ $roleFilter == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>

                <select name="status" class="bg-slate-950 border border-slate-800 text-white text-xs rounded-xl px-3 py-2 focus:outline-none focus:border-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                <button type="submit" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition-all">
                    <i class="fa-solid fa-filter text-xs"></i>
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-full text-left text-sm text-slate-300 whitespace-nowrap">
                <thead class="bg-slate-950/60 text-xs font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-4">Administrator</th>
                        <th class="px-6 py-4">Assigned Role</th>
                        <th class="px-6 py-4">Mode Scope</th>
                        <th class="px-6 py-4">Account Status</th>
                        <th class="px-6 py-4">Invitation Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($admins as $admin)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-400 flex items-center justify-center text-white font-bold text-xs uppercase shadow-md shadow-indigo-500/20">
                                        {{ substr($admin->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-white flex items-center gap-2">
                                            {{ $admin->name }}
                                            @if($admin->id === auth('admin')->id())
                                                <span class="text-[10px] bg-slate-800 text-slate-300 px-1.5 py-0.5 rounded border border-slate-700">You</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-400 mt-0.5">{{ $admin->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @foreach($admin->roles as $r)
                                    @if($r->slug === 'super-admin')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            <i class="fa-solid fa-crown text-[10px]"></i> Super Admin
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                            <i class="fa-solid fa-id-badge text-[10px]"></i> {{ $r->name }}
                                        </span>
                                    @endif
                                @endforeach
                            </td>
                            <td class="px-6 py-4">
                                @if($admin->isSuperAdmin())
                                    <span class="inline-flex items-center gap-1 text-xs text-emerald-400 font-medium">
                                        <i class="fa-solid fa-globe text-[11px]"></i> All Modes
                                    </span>
                                @else
                                    @php $allowedModes = $admin->getAllowedModes(); @endphp
                                    @if($allowedModes->isEmpty())
                                        <span class="text-xs text-slate-500 italic">No modes</span>
                                    @else
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @foreach($allowedModes as $mode)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-slate-800 text-slate-300 border border-slate-700">
                                                    @if($mode->icon)<i class="{{ $mode->icon }} text-[9px] text-indigo-400"></i>@endif
                                                    {{ $mode->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($admin->status === 'active')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-400 border border-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php $latestInvite = $admin->invitations->first(); @endphp
                                @if(!$latestInvite)
                                    <span class="text-xs text-slate-500">Directly Provisioned</span>
                                @elseif($latestInvite->isAccepted())
                                    <span class="inline-flex items-center gap-1 text-xs text-emerald-400">
                                        <i class="fa-solid fa-check text-[10px]"></i> Accepted
                                    </span>
                                @elseif($latestInvite->isExpired())
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1 text-xs text-rose-400">
                                            <i class="fa-solid fa-clock text-[10px]"></i> Expired
                                        </span>
                                        <form action="{{ route('admin.admins.resendInvitation', $admin) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-[11px] text-indigo-400 hover:text-indigo-300 underline font-medium">Resend</button>
                                        </form>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1 text-xs text-amber-400">
                                            <i class="fa-solid fa-hourglass-half text-[10px]"></i> Pending
                                        </span>
                                        <form action="{{ route('admin.admins.resendInvitation', $admin) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-[11px] text-indigo-400 hover:text-indigo-300 underline font-medium">Resend</button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.admins.edit', $admin) }}" class="p-2 rounded-xl bg-slate-800 hover:bg-indigo-600/30 text-slate-300 hover:text-indigo-400 transition-all text-xs" title="Edit Admin">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    @if($admin->id !== auth('admin')->id() && !$admin->isSuperAdmin())
                                        <form action="{{ route('admin.admins.toggleStatus', $admin) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="p-2 rounded-xl bg-slate-800 hover:bg-amber-600/30 text-slate-300 hover:text-amber-400 transition-all text-xs" title="Toggle Status">
                                                <i class="fa-solid fa-power-off"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete administrator {{ $admin->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded-xl bg-slate-800 hover:bg-rose-600/30 text-slate-300 hover:text-rose-400 transition-all text-xs" title="Delete Admin">
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
                                <i class="fa-solid fa-users text-3xl mb-3 text-slate-600 block"></i>
                                No administrators found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($admins->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                {{ $admins->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@extends('admin.layouts.admin')

@section('title', 'Edit Administrator: ' . $admin->name . ' — Shopy Admin')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.admins.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors mb-2">
                <i class="fa-solid fa-arrow-left"></i> Back to Administrators
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Edit Administrator: {{ $admin->name }}</h1>
                @if($admin->isSuperAdmin())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        <i class="fa-solid fa-crown text-[10px]"></i> Super Admin
                    </span>
                @endif
            </div>
            <p class="text-slate-400 text-sm mt-1">Modify staff profile, change assigned role, and customize shopping mode access.</p>
        </div>
    </div>

    <!-- Error Summary -->
    @if ($errors->any())
        <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-sm">
            <div class="font-semibold mb-1 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i> Please resolve the following errors:
            </div>
            <ul class="list-disc list-inside text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.admins.update', $admin) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Account Profile Details -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-user-shield text-indigo-400"></i> Staff Credentials
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Full Name <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $admin->name) }}" required
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm placeholder-slate-500 focus:outline-none transition-all">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Email Address <span class="text-rose-400">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email', $admin->email) }}" required
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm placeholder-slate-500 focus:outline-none transition-all">
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Contact Phone
                    </label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $admin->phone) }}" placeholder="e.g. +1 555-0199"
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm placeholder-slate-500 focus:outline-none transition-all">
                </div>

                <!-- Primary Role Assignment -->
                <div>
                    <label for="role_id" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Assigned Role <span class="text-rose-400">*</span>
                    </label>
                    <select name="role_id" id="role_id" required
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none transition-all">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ (old('role_id', $currentRole?->id) == $role->id) ? 'selected' : '' }}>
                                {{ $role->name }} ({{ $role->permissions->count() }} permissions)
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Account Status <span class="text-rose-400">*</span>
                    </label>
                    <select name="status" id="status" required
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none transition-all">
                        <option value="active" {{ old('status', $admin->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $admin->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Mode Access Assignment -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-amber-400"></i> Shopping Mode Access Scopes
                </h2>
                <p class="text-xs text-slate-400 mt-1">
                    Direct mode overrides assigned to this administrator.
                    <span class="text-slate-500">(Inherited role modes will also apply automatically)</span>.
                </p>
            </div>

            @if($admin->isSuperAdmin())
                <div class="p-4 bg-amber-500/10 border border-amber-500/20 rounded-2xl flex items-center gap-3 text-amber-400 text-xs">
                    <i class="fa-solid fa-crown text-sm"></i>
                    <span>Super Administrator has unrestricted global access to all current and future shopping modes.</span>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($modes as $mode)
                    <label class="relative flex items-start p-4 rounded-2xl bg-slate-950 border border-slate-800 hover:border-slate-700 cursor-pointer transition-all has-[:checked]:border-indigo-500/50 has-[:checked]:bg-indigo-500/5">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="modes[]" value="{{ $mode->id }}" class="w-4 h-4 rounded text-indigo-600 bg-slate-900 border-slate-700 focus:ring-indigo-500"
                                {{ in_array($mode->id, old('modes', $assignedModeIds)) ? 'checked' : '' }}>
                        </div>
                        <div class="ml-3 text-sm">
                            <div class="font-semibold text-white flex items-center gap-1.5">
                                @if($mode->icon)<i class="{{ $mode->icon }} text-xs text-indigo-400"></i>@endif
                                {{ $mode->name }}
                            </div>
                            <p class="text-[11px] text-slate-400 mt-0.5">{{ $mode->description ?? 'Channel scope' }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Submission Buttons -->
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('admin.admins.index') }}" class="px-6 py-2.5 rounded-xl border border-slate-800 hover:bg-slate-800 text-slate-300 text-sm font-medium transition-all">
                Cancel
            </a>
            <button type="submit" class="px-8 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold shadow-lg shadow-indigo-600/30 transition-all">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection

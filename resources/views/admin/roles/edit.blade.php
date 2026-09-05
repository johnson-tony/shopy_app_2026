@extends('admin.layouts.admin')

@section('title', 'Edit Role: ' . $role->name . ' — Shopy Admin')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors mb-2">
                <i class="fa-solid fa-arrow-left"></i> Back to Roles
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Edit Role: {{ $role->name }}</h1>
                @if($role->slug === 'super-admin')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        <i class="fa-solid fa-crown text-[10px]"></i> System Super Admin
                    </span>
                @endif
            </div>
            <p class="text-slate-400 text-sm mt-1">Configure granted permissions and mode assignments for this role.</p>
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

    <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Basic Role Details Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-id-badge text-indigo-400"></i> Role Information
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Role Name -->
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Role Name <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" required
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm placeholder-slate-500 focus:outline-none transition-all">
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Role Slug
                    </label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $role->slug) }}"
                        {{ $role->slug === 'super-admin' ? 'readonly' : '' }}
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm font-mono placeholder-slate-500 focus:outline-none transition-all {{ $role->slug === 'super-admin' ? 'opacity-60 cursor-not-allowed' : '' }}">
                    @if($role->slug === 'super-admin')
                        <p class="text-[11px] text-amber-400/80 mt-1">Super Admin slug is permanent and required by the authorization engine.</p>
                    @endif
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Description
                    </label>
                    <textarea name="description" id="description" rows="2"
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm placeholder-slate-500 focus:outline-none transition-all">{{ old('description', $role->description) }}</textarea>
                </div>

                <!-- Status Switch -->
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="status" value="1" class="sr-only peer"
                            {{ $role->slug === 'super-admin' ? 'disabled' : '' }}
                            {{ old('status', $role->status) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                    <span class="text-sm font-medium text-slate-300">Active Role</span>
                </div>
            </div>
        </div>

        <!-- Mode Access Assignment Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-layer-group text-amber-400"></i> Mode-Based Access Scope
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">Specify which shopping channels this role can view, manage, and edit.</p>
                </div>
                <button type="button" id="toggleAllModes" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition-colors">
                    Toggle All Modes
                </button>
            </div>

            @if($role->slug === 'super-admin')
                <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl flex items-center gap-3 text-emerald-400 text-xs">
                    <i class="fa-solid fa-circle-check text-sm"></i>
                    <span>Super Admin has global, automatic access to all shopping channels, current and future.</span>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($modes as $mode)
                    <label class="relative flex items-start p-4 rounded-2xl bg-slate-950 border border-slate-800 hover:border-slate-700 cursor-pointer transition-all has-[:checked]:border-indigo-500/50 has-[:checked]:bg-indigo-500/5">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="modes[]" value="{{ $mode->id }}" class="mode-checkbox w-4 h-4 rounded text-indigo-600 bg-slate-900 border-slate-700 focus:ring-indigo-500 focus:ring-offset-slate-900"
                                {{ in_array($mode->id, old('modes', $assignedModeIds)) ? 'checked' : '' }}>
                        </div>
                        <div class="ml-3 text-sm">
                            <div class="font-semibold text-white flex items-center gap-2">
                                @if($mode->icon)<i class="{{ $mode->icon }} text-xs text-indigo-400"></i>@endif
                                {{ $mode->name }}
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $mode->description ?? 'Channel access' }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Permissions Matrix Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-key text-cyan-400"></i> Permissions Matrix
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">Select exact administrative actions granted to this role.</p>
                </div>
                <button type="button" id="toggleAllPerms" class="text-xs font-semibold text-cyan-400 hover:text-cyan-300 transition-colors">
                    Toggle All Permissions
                </button>
            </div>

            @if($role->slug === 'super-admin')
                <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl flex items-center gap-3 text-emerald-400 text-xs">
                    <i class="fa-solid fa-infinity text-sm"></i>
                    <span>Super Administrator automatically bypasses permission checks for all current and future modules.</span>
                </div>
            @endif

            <div class="space-y-6">
                @foreach($permissionsByModule as $moduleName => $modulePerms)
                    <div class="bg-slate-950 border border-slate-800/80 rounded-2xl p-5 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800/60">
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                {{ $moduleName }} Module
                                <span class="text-xs text-slate-500 font-normal">({{ $modulePerms->count() }})</span>
                            </h3>
                            <button type="button" class="text-xs text-slate-400 hover:text-white select-module-btn transition-colors" data-module="{{ Str::slug($moduleName) }}">
                                Select All in Module
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach($modulePerms as $permission)
                                <label class="flex items-start p-3 rounded-xl bg-slate-900/60 border border-slate-800 hover:border-slate-700 cursor-pointer transition-colors has-[:checked]:border-indigo-500/40 has-[:checked]:bg-indigo-500/10">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                            class="perm-checkbox perm-module-{{ Str::slug($moduleName) }} w-4 h-4 rounded text-indigo-600 bg-slate-950 border-slate-700 focus:ring-indigo-500"
                                            {{ in_array($permission->id, old('permissions', $assignedPermissionIds)) ? 'checked' : '' }}>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-xs font-semibold text-white">{{ $permission->name }}</p>
                                        <code class="text-[10px] text-slate-500 font-mono block mt-0.5">{{ $permission->slug }}</code>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Submission Buttons -->
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('admin.roles.index') }}" class="px-6 py-2.5 rounded-xl border border-slate-800 hover:bg-slate-800 text-slate-300 text-sm font-medium transition-all">
                Cancel
            </a>
            <button type="submit" class="px-8 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold shadow-lg shadow-indigo-600/30 transition-all">
                Save Changes
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle all modes
        const toggleAllModes = document.getElementById('toggleAllModes');
        toggleAllModes?.addEventListener('click', function () {
            const boxes = document.querySelectorAll('.mode-checkbox');
            const anyUnchecked = Array.from(boxes).some(b => !b.checked);
            boxes.forEach(b => b.checked = anyUnchecked);
        });

        // Toggle all permissions
        const toggleAllPerms = document.getElementById('toggleAllPerms');
        toggleAllPerms?.addEventListener('click', function () {
            const boxes = document.querySelectorAll('.perm-checkbox');
            const anyUnchecked = Array.from(boxes).some(b => !b.checked);
            boxes.forEach(b => b.checked = anyUnchecked);
        });

        // Select all in specific module
        document.querySelectorAll('.select-module-btn').forEach(button => {
            button.addEventListener('click', function () {
                const module = this.dataset.module;
                const boxes = document.querySelectorAll('.perm-module-' + module);
                const anyUnchecked = Array.from(boxes).some(b => !b.checked);
                boxes.forEach(b => b.checked = anyUnchecked);
            });
        });
    });
</script>
@endsection

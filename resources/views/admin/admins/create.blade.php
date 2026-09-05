@extends('admin.layouts.admin')

@section('title', 'Invite Sub-Administrator — Shopy Admin')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.admins.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors mb-2">
                <i class="fa-solid fa-arrow-left"></i> Back to Administrators
            </a>
            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Invite Sub-Administrator</h1>
            <p class="text-slate-400 text-sm mt-1">Dispatch a secure email invitation with custom role and mode access scopes.</p>
        </div>
    </div>

    <!-- Security Notification Banner -->
    <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-2xl p-4 flex items-start gap-3">
        <i class="fa-solid fa-shield-halved text-indigo-400 text-base mt-0.5"></i>
        <div class="text-xs text-indigo-300/90 leading-relaxed">
            <strong>Zero Plaintext Password Policy:</strong> For maximum security, you do not set a password here. The invited administrator will receive a cryptographically signed, single-use link (valid for 48 hours) to create their own confidential password.
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

    <form action="{{ route('admin.admins.store') }}" method="POST" class="space-y-8">
        @csrf

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
                    <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="e.g. John Doe" required
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm placeholder-slate-500 focus:outline-none transition-all">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Email Address <span class="text-rose-400">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="e.g. john@shopy.test" required
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm placeholder-slate-500 focus:outline-none transition-all">
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Contact Phone
                    </label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="e.g. +1 555-0199"
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm placeholder-slate-500 focus:outline-none transition-all">
                </div>

                <!-- Primary Role Assignment -->
                <div>
                    <label for="role_id" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Assigned Role <span class="text-rose-400">*</span>
                    </label>
                    <select name="role_id" id="role_id" required
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none transition-all">
                        <option value="">Choose a role...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }} ({{ $role->permissions->count() }} permissions)
                            </option>
                        @endforeach
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
                    Select specific shopping modes this administrator is authorized to view and manage.
                    <span class="text-slate-500">(Inherited role modes will also apply automatically)</span>.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($modes as $mode)
                    <label class="relative flex items-start p-4 rounded-2xl bg-slate-950 border border-slate-800 hover:border-slate-700 cursor-pointer transition-all has-[:checked]:border-indigo-500/50 has-[:checked]:bg-indigo-500/5">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="modes[]" value="{{ $mode->id }}" class="w-4 h-4 rounded text-indigo-600 bg-slate-900 border-slate-700 focus:ring-indigo-500"
                                {{ in_array($mode->id, old('modes', [])) ? 'checked' : '' }}>
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
            <button type="submit" class="px-8 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
                <i class="fa-solid fa-paper-plane text-xs"></i> Send Invitation
            </button>
        </div>
    </form>
</div>
@endsection

@extends('admin.layouts.admin')

@section('title', 'Login as User')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-xl space-y-6">
        <div class="flex items-start gap-4">
            <span class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-500/15 border border-indigo-500/30 text-indigo-400 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </span>
            <div>
                <h1 class="text-xl font-bold text-white">Opening Storefront as {{ $user->name }}</h1>
                <p class="text-sm text-slate-400 mt-1">
                    The storefront opens in a <strong class="text-slate-200">new tab</strong> on its own separate session.
                    You remain logged in as Admin in this tab — logging out of one never affects the other.
                </p>
            </div>
        </div>

        <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-950 border border-slate-800 px-4 py-3">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-xs font-bold text-indigo-400">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </span>
                <div>
                    <p class="text-sm font-medium text-white">{{ $user->name }}</p>
                    <p class="text-xs text-slate-500 font-mono">{{ $user->email }}</p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-emerald-950 text-emerald-400 border border-emerald-800/60 text-[11px] font-bold">
                Signed link · 5 min
            </span>
        </div>

        <a href="{{ $impersonateUrl }}" target="_blank" id="impersonateLink"
           class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a1 1 0 110 2h-1a1 1 0 100 2H9a1 1 0 110-2h1zm0 0h2M4 7a2 2 0 012-2h2m0 4h2a2 2 0 012-2V5m0 6h-2a2 2 0 00-2 2v1a2 2 0 01-2 2H5m0 0v2" />
            </svg>
            Open Storefront as {{ $user->name }}
        </a>

        <div class="pt-2 border-t border-slate-800">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition">
                &larr; Back to User Management
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const link = document.getElementById('impersonateLink');
    if (link) {
        // Try to open in a new tab automatically on page load.
        window.open(link.href, '_blank');
    }
});
</script>
@endpush

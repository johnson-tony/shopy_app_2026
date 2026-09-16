@extends('admin.layouts.admin')

@section('title', 'Customer Support Helpdesk')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-xs font-semibold text-indigo-400 mb-2">
                <i class="fa-solid fa-headset text-xs"></i>
                <span>Customer Care &amp; Partner Escalations</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Support Helpdesk</h1>
            <p class="text-slate-400 text-sm mt-1">
                Triage user inquiries, resolve order &amp; delivery delays, manage rider escalations, and chat with customers.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="tel:+916379644145" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 font-bold text-xs transition">
                <i class="fa-solid fa-phone text-emerald-400"></i>
                <span>Central Helpline Desk</span>
            </a>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Tickets</p>
            <p class="text-2xl font-black text-white mt-1.5">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-slate-900 border {{ $stats['open'] > 0 ? 'border-amber-500/40 bg-amber-950/20' : 'border-slate-800' }} rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold {{ $stats['open'] > 0 ? 'text-amber-400' : 'text-slate-400' }} uppercase tracking-wider">Open</p>
            <p class="text-2xl font-black {{ $stats['open'] > 0 ? 'text-amber-300' : 'text-white' }} mt-1.5 flex items-center gap-2">
                @if($stats['open'] > 0)
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                @endif
                <span>{{ number_format($stats['open']) }}</span>
            </p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">In Progress</p>
            <p class="text-2xl font-black text-blue-400 mt-1.5">{{ number_format($stats['inProgress']) }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Resolved</p>
            <p class="text-2xl font-black text-emerald-400 mt-1.5">{{ number_format($stats['resolved']) }}</p>
        </div>
        <div class="bg-slate-900 border {{ $stats['partnerIssues'] > 0 ? 'border-rose-500/40 bg-rose-950/20' : 'border-slate-800' }} rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold {{ $stats['partnerIssues'] > 0 ? 'text-rose-400' : 'text-slate-400' }} uppercase tracking-wider">Partner Escalations</p>
            <p class="text-2xl font-black {{ $stats['partnerIssues'] > 0 ? 'text-rose-300' : 'text-white' }} mt-1.5 flex items-center gap-2">
                @if($stats['partnerIssues'] > 0)
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                @endif
                <span>{{ number_format($stats['partnerIssues']) }}</span>
            </p>
        </div>
    </div>

    <!-- Quick Status Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        <a href="{{ route('admin.support.index', array_filter(['search' => $search ?: null, 'category' => $categoryFilter ?: null, 'priority' => $priorityFilter ?: null])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 cursor-pointer {{ $statusFilter === 'all' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' }}">
            <span>All Tickets</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $statusFilter === 'all' ? 'bg-white/20 text-white' : 'bg-slate-800 text-slate-400' }}">
                {{ $stats['total'] }}
            </span>
        </a>

        <a href="{{ route('admin.support.index', array_filter(['status' => 'open', 'search' => $search ?: null, 'category' => $categoryFilter ?: null, 'priority' => $priorityFilter ?: null])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 cursor-pointer {{ $statusFilter === 'open' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' }}">
            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            <span>Open</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $statusFilter === 'open' ? 'bg-white/20 text-white' : 'bg-amber-950/80 text-amber-400 border border-amber-800' }}">
                {{ $stats['open'] }}
            </span>
        </a>

        <a href="{{ route('admin.support.index', array_filter(['status' => 'in_progress', 'search' => $search ?: null, 'category' => $categoryFilter ?: null, 'priority' => $priorityFilter ?: null])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 cursor-pointer {{ $statusFilter === 'in_progress' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' }}">
            <span>In Progress</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $statusFilter === 'in_progress' ? 'bg-white/20 text-white' : 'bg-slate-800 text-slate-400' }}">
                {{ $stats['inProgress'] }}
            </span>
        </a>

        <a href="{{ route('admin.support.index', array_filter(['status' => 'resolved', 'search' => $search ?: null, 'category' => $categoryFilter ?: null, 'priority' => $priorityFilter ?: null])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 cursor-pointer {{ $statusFilter === 'resolved' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' }}">
            <i class="fa-solid fa-check text-[10px]"></i>
            <span>Resolved</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $statusFilter === 'resolved' ? 'bg-white/20 text-white' : 'bg-slate-800 text-slate-400' }}">
                {{ $stats['resolved'] }}
            </span>
        </a>

        <a href="{{ route('admin.support.index', array_filter(['status' => 'closed', 'search' => $search ?: null, 'category' => $categoryFilter ?: null, 'priority' => $priorityFilter ?: null])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 cursor-pointer {{ $statusFilter === 'closed' ? 'bg-slate-700 text-white' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' }}">
            <span>Closed</span>
        </a>

        <a href="{{ route('admin.support.index', array_filter(['category' => 'partner_issue', 'search' => $search ?: null, 'status' => $statusFilter !== 'all' ? $statusFilter : null])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 cursor-pointer {{ $categoryFilter === 'partner_issue' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/30' : 'bg-slate-900 border border-slate-800 text-rose-400 hover:text-white' }}">
            <i class="fa-solid fa-person-biking text-xs"></i>
            <span>Partner Issues</span>
        </a>
    </div>

    <!-- Search & Filters Toolbar -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.support.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @if($statusFilter && $statusFilter !== 'all')
                <input type="hidden" name="status" value="{{ $statusFilter }}">
            @endif

            <div class="relative lg:col-span-2">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by Ticket #, Name, Email, Subject, or Order #..."
                       class="w-full bg-slate-800/80 border border-slate-700 rounded-xl pl-9 pr-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <select name="category" onchange="this.form.submit()" class="w-full bg-slate-800/80 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Categories</option>
                    @foreach(\App\Models\SupportTicket::CATEGORIES as $key => $label)
                        <option value="{{ $key }}" {{ $categoryFilter === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <select name="priority" onchange="this.form.submit()" class="w-full bg-slate-800/80 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Priorities</option>
                    <option value="low" {{ $priorityFilter === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="normal" {{ $priorityFilter === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="high" {{ $priorityFilter === 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ $priorityFilter === 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
                @if($search || $categoryFilter || $priorityFilter || ($statusFilter && $statusFilter !== 'all'))
                    <a href="{{ route('admin.support.index') }}" class="px-3 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white text-xs font-bold transition shrink-0" title="Reset Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tickets Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-950/40 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Ticket</th>
                        <th class="py-3.5 px-4">Customer</th>
                        <th class="py-3.5 px-4">Subject &amp; Category</th>
                        <th class="py-3.5 px-4">Linked Order / Rider</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Activity</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-xs">
                    @forelse($tickets as $ticket)
                        @php
                            $statusBadge = $ticket->status_badge;
                            $priorityBadge = $ticket->priority_badge;
                            $partner = $ticket->order?->deliveryPartner;
                        @endphp
                        <tr class="hover:bg-slate-800/40 transition">
                            <!-- Ticket # & Priority -->
                            <td class="py-4 px-4 font-medium">
                                <a href="{{ route('admin.support.show', $ticket->id) }}" class="font-mono font-bold text-indigo-400 hover:text-indigo-300">
                                    #{{ $ticket->ticket_number }}
                                </a>
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $priorityBadge['bg'] }} {{ $priorityBadge['text'] }}">
                                        {{ $priorityBadge['label'] }}
                                    </span>
                                </div>
                            </td>

                            <!-- Customer Info -->
                            <td class="py-4 px-4">
                                <div class="font-bold text-white">{{ $ticket->name }}</div>
                                <div class="text-slate-400 text-[11px]">{{ $ticket->email }}</div>
                                @if($ticket->phone)
                                    <div class="text-slate-500 text-[11px] flex items-center gap-1 mt-0.5">
                                        <i class="fa-solid fa-phone text-[9px]"></i>
                                        <span>{{ $ticket->phone }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Subject & Category -->
                            <td class="py-4 px-4 max-w-xs">
                                <a href="{{ route('admin.support.show', $ticket->id) }}" class="font-bold text-slate-200 hover:text-indigo-400 block truncate" title="{{ $ticket->subject }}">
                                    {{ $ticket->subject }}
                                </a>
                                <span class="text-[11px] text-slate-400 inline-block mt-0.5">
                                    {{ $ticket->category_label }}
                                </span>
                            </td>

                            <!-- Linked Order & Delivery Partner -->
                            <td class="py-4 px-4">
                                @if($ticket->order)
                                    <div>
                                        <a href="{{ route('admin.orders.show', $ticket->order->order_number) }}" class="font-bold text-indigo-400 hover:text-indigo-300 font-mono text-[11px]">
                                            #{{ $ticket->order->order_number }}
                                        </a>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 ml-1">
                                            {{ ucfirst($ticket->order->status) }}
                                        </span>
                                    </div>

                                    @if($partner)
                                        <div class="mt-1.5 flex items-center gap-1.5 text-[11px] text-emerald-400">
                                            <i class="fa-solid fa-person-biking text-[10px]"></i>
                                            <span class="font-semibold">{{ $partner->name }}</span>
                                            @if($partner->phone)
                                                <a href="tel:{{ $partner->phone }}" class="text-slate-400 hover:text-emerald-400 transition" title="Call Delivery Partner">
                                                    <i class="fa-solid fa-phone text-[10px]"></i>
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <span class="text-slate-500 text-[11px]">General Helpdesk</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }}">
                                    <i class="{{ $statusBadge['icon'] }} text-[10px]"></i>
                                    <span>{{ $statusBadge['label'] }}</span>
                                </span>
                                @if($ticket->partner_contacted)
                                    <div class="mt-1">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-950/60 text-emerald-400 border border-emerald-800">
                                            <i class="fa-solid fa-phone text-[8px]"></i>
                                            <span>Rider Contacted</span>
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <!-- Activity -->
                            <td class="py-4 px-4 text-slate-400 text-[11px]">
                                <div class="flex items-center gap-1.5 font-semibold text-slate-300">
                                    <i class="fa-solid fa-comment text-slate-500"></i>
                                    <span>{{ $ticket->messages_count }} message(s)</span>
                                </div>
                                <div class="mt-0.5">
                                    {{ $ticket->updated_at->diffForHumans() }}
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-4 text-right">
                                <a href="{{ route('admin.support.show', $ticket->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600/20 hover:bg-indigo-600 text-indigo-400 hover:text-white border border-indigo-500/30 font-bold text-xs transition cursor-pointer">
                                    <i class="fa-solid fa-message text-[11px]"></i>
                                    <span>Open Chat</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-500">
                                <i class="fa-solid fa-inbox text-3xl text-slate-600 mb-2 block"></i>
                                <span class="text-sm font-semibold">No support tickets found matching criteria.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/30">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

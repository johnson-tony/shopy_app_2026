@extends('user.layouts.app')

@section('title', 'Help & Customer Support | ' . config('app.name', 'Shopy'))

@section('content')
<div class="min-h-screen bg-slate-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto space-y-8">
        <!-- Header Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-slate-900 text-white p-6 sm:p-10 shadow-2xl">
            <div class="absolute -right-12 -bottom-12 w-64 h-64 rounded-full bg-indigo-600/20 blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-xs font-semibold">
                        <i class="fa-solid fa-headset"></i>
                        <span>24/7 Dedicated Support Center</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">How can we help you today?</h1>
                    <p class="text-slate-300 text-sm max-w-xl">
                        Track live orders, resolve delivery or return delays, query payments, or speak with our customer care representatives.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="{{ route('support.create') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs sm:text-sm shadow-lg shadow-indigo-600/30 transition cursor-pointer">
                        <i class="fa-solid fa-plus"></i>
                        <span>Open Support Ticket</span>
                    </a>
                    <a href="tel:+916379644145" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs sm:text-sm transition cursor-pointer">
                        <i class="fa-solid fa-phone-volume text-emerald-400"></i>
                        <span>Call Helpline</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Helplines Strip -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-phone-volume"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Toll-Free Phone Support</span>
                    <a href="tel:+916379644145" class="text-sm font-black text-slate-800 hover:text-emerald-600 transition block mt-0.5">
                        +91 63796 44145
                    </a>
                    <span class="text-[11px] text-slate-500">Mon - Sun: 7:00 AM - 11:00 PM</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Email Desk</span>
                    <a href="mailto:support@formann.in" class="text-sm font-black text-slate-800 hover:text-indigo-600 transition block mt-0.5">
                        support@formann.in
                    </a>
                    <span class="text-[11px] text-slate-500">Average response: &lt; 30 mins</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-brands fa-whatsapp"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">WhatsApp Chat</span>
                    <a href="https://wa.me/916379644145?text=Hello%20Support" target="_blank" class="text-sm font-black text-slate-800 hover:text-emerald-600 transition block mt-0.5">
                        Chat on WhatsApp &rarr;
                    </a>
                    <span class="text-[11px] text-slate-500">Instant agent connection</span>
                </div>
            </div>
        </div>

        <!-- Recent Tickets Section -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-ticket text-indigo-600"></i>
                        <span>My Support Requests &amp; Order Chats</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Review current tickets, order resolution chats, and staff responses.</p>
                </div>

                @if($openCount > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        {{ $openCount }} Pending Resolution
                    </span>
                @endif
            </div>

            @if(!Auth::check())
                <div class="text-center py-10 px-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                    <i class="fa-solid fa-user-lock text-slate-400 text-2xl mb-2"></i>
                    <h3 class="text-sm font-bold text-slate-700">Please Sign In to View Your Support Tickets</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Sign in to track ongoing queries and talk to our customer support representatives about your orders.
                    </p>
                    <div class="mt-4 flex items-center justify-center gap-3">
                        <a href="{{ route('login') }}" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow transition">Sign In</a>
                        <a href="{{ route('support.create') }}" class="px-5 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition">Submit as Guest</a>
                    </div>
                </div>
            @elseif($myTickets->isEmpty())
                <div class="text-center py-12 px-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                    <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3 text-xl">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">No active support issues</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        All your previous queries have been resolved or you have not submitted any support tickets yet.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('support.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition">
                            <i class="fa-solid fa-plus"></i>
                            <span>Create Support Request</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($myTickets as $ticket)
                        @php $badge = $ticket->status_badge; @endphp
                        <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/50 p-2 rounded-2xl transition">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('support.show', $ticket->ticket_number) }}" class="font-mono font-bold text-xs text-indigo-600 hover:underline">
                                        #{{ $ticket->ticket_number }}
                                    </a>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $badge['bg'] }} {{ $badge['text'] }} border {{ $badge['border'] }}">
                                        <i class="{{ $badge['icon'] }} text-[9px]"></i>
                                        <span>{{ $badge['label'] }}</span>
                                    </span>
                                    @if($ticket->order)
                                        <a href="{{ route('orders.show', $ticket->order->order_number) }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-mono font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200">
                                            <i class="fa-solid fa-box text-[9px]"></i> Order #{{ $ticket->order->order_number }}
                                        </a>
                                    @endif
                                </div>
                                <h3 class="text-sm font-bold text-slate-900">
                                    <a href="{{ route('support.show', $ticket->ticket_number) }}" class="hover:text-indigo-600 transition">
                                        {{ $ticket->subject }}
                                    </a>
                                </h3>
                                <p class="text-xs text-slate-500">
                                    Category: {{ $ticket->category_label }} &bull; Updated {{ $ticket->updated_at->diffForHumans() }} &bull; {{ $ticket->messages->count() }} message(s)
                                </p>
                            </div>

                            <a href="{{ route('support.show', $ticket->ticket_number) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 hover:bg-indigo-600 text-white font-bold text-xs shadow-sm transition text-center shrink-0">
                                <span>Open Chat</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    @endforeach
                </div>

                @if($myTickets instanceof \Illuminate\Pagination\LengthAwarePaginator && $myTickets->hasPages())
                    <div class="pt-4 border-t border-slate-100">
                        {{ $myTickets->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection

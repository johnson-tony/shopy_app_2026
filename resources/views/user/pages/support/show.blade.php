@extends('user.layouts.app')

@section('title', 'Ticket #' . $ticket->ticket_number . ' - ' . $ticket->subject . ' | ' . config('app.name', 'Shopy'))

@section('content')
<div class="min-h-screen bg-slate-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Breadcrumb & Helpline Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                <a href="{{ route('support.index') }}" class="hover:text-indigo-600 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Support Center</span>
                </a>
                <span>/</span>
                <span class="text-slate-800 font-bold font-mono">#{{ $ticket->ticket_number }}</span>
            </div>

            <div class="flex items-center gap-3">
                <a href="tel:+916379644145" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold hover:bg-emerald-100 transition cursor-pointer">
                    <i class="fa-solid fa-phone"></i>
                    <span>Helpline: +91 63796 44145</span>
                </a>
                <a href="https://wa.me/916379644145?text=Hello%20Support%20Ticket%20{{ $ticket->ticket_number }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-500 transition cursor-pointer shadow-xs">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>WhatsApp</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-semibold flex items-center gap-2 shadow-xs">
                <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Ticket Header Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-mono text-xs font-extrabold text-indigo-600 bg-indigo-50 border border-indigo-100 px-2.5 py-0.5 rounded-lg">
                            #{{ $ticket->ticket_number }}
                        </span>
                        @php $statusBadge = $ticket->status_badge; @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }}">
                            <i class="{{ $statusBadge['icon'] }} text-[10px]"></i>
                            <span>{{ $statusBadge['label'] }}</span>
                        </span>
                        @php $priorityBadge = $ticket->priority_badge; @endphp
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $priorityBadge['bg'] }} {{ $priorityBadge['text'] }}">
                            <span>{{ $priorityBadge['label'] }} Priority</span>
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1">
                        {{ $ticket->subject }}
                    </h1>
                </div>

                <div class="text-right text-xs text-slate-400 shrink-0">
                    <span class="block">Opened on {{ $ticket->created_at->format('d M Y, h:i A') }}</span>
                    <span class="font-semibold text-slate-600">Category: {{ $ticket->category_label }}</span>
                </div>
            </div>

            <!-- Linked Order Card (If attached to order) -->
            @if($ticket->order)
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-base shadow-xs shrink-0">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900">Order #{{ $ticket->order->order_number }}</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-700">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->order->status)) }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                ₹{{ number_format($ticket->order->grand_total, 2) }} &bull; {{ $ticket->order->items->count() }} item(s) &bull; {{ $ticket->order->created_at->format('d M Y') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('orders.show', $ticket->order->order_number) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:text-indigo-600 hover:border-indigo-200 text-xs font-bold transition shadow-xs">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                            <span>View Order Tracking</span>
                        </a>
                    </div>
                </div>

                <!-- Delivery Partner Call Card (If rider assigned) -->
                @if($ticket->order->deliveryPartner)
                    <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg shadow-xs shrink-0">
                                <i class="fa-solid fa-person-biking"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-extrabold text-slate-900">{{ $ticket->order->deliveryPartner->name }}</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800">
                                        Assigned Delivery Rider
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 mt-0.5">
                                    {{ ucfirst($ticket->order->deliveryPartner->vehicle_type ?? 'Bike') }} &bull; {{ $ticket->order->deliveryPartner->vehicle_number ?? 'Fleet Unit' }}
                                </p>
                            </div>
                        </div>

                        @if($ticket->order->deliveryPartner->phone)
                            <a href="tel:{{ $ticket->order->deliveryPartner->phone }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-sm shadow-emerald-600/20 transition cursor-pointer shrink-0">
                                <i class="fa-solid fa-phone"></i>
                                <span>Call Driver Directly</span>
                            </a>
                        @endif
                    </div>
                @endif
            @endif
        </div>

        <!-- Chat Stream Section -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <!-- Chat Header -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                    <i class="fa-solid fa-comments text-indigo-600"></i>
                    <span>Conversation History ({{ $ticket->messages->where('is_internal', false)->count() }} messages)</span>
                </div>
                <span class="text-[11px] text-slate-400">Live Agent Response Desk</span>
            </div>

            <!-- Messages Timeline List -->
            <div class="p-6 space-y-6 max-h-[600px] overflow-y-auto bg-slate-50/50">
                @php
                    $customerMessages = $ticket->messages->where('is_internal', false);
                @endphp

                @forelse($customerMessages as $msg)
                    @php
                        $isMe = $msg->sender_type === \App\Models\SupportMessage::SENDER_USER;
                    @endphp

                    <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-xl flex gap-3 {{ $isMe ? 'flex-row-reverse' : 'flex-row' }}">
                            <!-- Avatar -->
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 {{ $isMe ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-white' }}">
                                @if($isMe)
                                    <i class="fa-solid fa-user"></i>
                                @else
                                    <i class="fa-solid fa-headset text-indigo-400"></i>
                                @endif
                            </div>

                            <!-- Bubble -->
                            <div class="space-y-1.5 {{ $isMe ? 'items-end' : 'items-start' }}">
                                <div class="flex items-center gap-2 text-[11px] {{ $isMe ? 'justify-end text-slate-500' : 'justify-start text-slate-500' }}">
                                    <span class="font-bold {{ $isMe ? 'text-indigo-600' : 'text-slate-800' }}">
                                        {{ $isMe ? 'You' : ($msg->admin?->name ?? 'Support Agent') }}
                                    </span>
                                    <span>&bull;</span>
                                    <span>{{ $msg->created_at->format('d M, h:i A') }}</span>
                                </div>

                                <div class="p-4 rounded-2xl text-xs sm:text-sm leading-relaxed whitespace-pre-line shadow-xs {{ $isMe ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-white border border-slate-200 text-slate-800 rounded-tl-none' }}">
                                    {{ $msg->message }}

                                    <!-- Attachment Preview if any -->
                                    @if($msg->attachment)
                                        @php
                                            $ext = pathinfo($msg->attachment, PATHINFO_EXTENSION);
                                            $isImg = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp']);
                                        @endphp
                                        <div class="mt-3 pt-3 border-t {{ $isMe ? 'border-indigo-500/50' : 'border-slate-100' }}">
                                            @if($isImg)
                                                <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank" class="block group relative rounded-xl overflow-hidden max-w-xs border {{ $isMe ? 'border-indigo-400/40' : 'border-slate-200' }}">
                                                    <img src="{{ asset('storage/' . $msg->attachment) }}" alt="Ticket Attachment" class="w-full max-h-48 object-cover group-hover:scale-105 transition">
                                                    <span class="absolute bottom-1 right-1 bg-black/60 text-white text-[10px] px-2 py-0.5 rounded">View full</span>
                                                </a>
                                            @else
                                                <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold {{ $isMe ? 'bg-indigo-700 text-white hover:bg-indigo-800' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }} transition">
                                                    <i class="fa-solid fa-file-pdf"></i>
                                                    <span>View Attachment File ({{ strtoupper($ext) }})</span>
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-400 text-xs">
                        No messages found in this conversation.
                    </div>
                @endforelse
            </div>

            <!-- Status Notice if Ticket is Resolved -->
            @if(in_array($ticket->status, [\App\Models\SupportTicket::STATUS_RESOLVED, \App\Models\SupportTicket::STATUS_CLOSED]))
                <div class="p-3.5 bg-emerald-50 border-t border-b border-emerald-200 text-emerald-800 text-xs flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                        <span>This support ticket is currently marked as <strong>{{ ucfirst($ticket->status) }}</strong>. Sending a new message below will automatically re-open it.</span>
                    </div>
                </div>
            @endif

            <!-- Reply Form -->
            <div class="p-4 sm:p-6 bg-white border-t border-slate-200">
                <form action="{{ route('support.reply', $ticket->ticket_number) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label for="message" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Send Reply to Support
                        </label>
                        <textarea id="message" name="message" rows="3" required placeholder="Type your reply, update, or additional questions here..." class="w-full px-4 py-3 rounded-2xl border border-slate-300 text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none placeholder:text-slate-400 transition resize-none"></textarea>
                        @error('message')
                            <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-dashed border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-semibold cursor-pointer transition">
                                <i class="fa-solid fa-paperclip text-slate-400"></i>
                                <span>Attach Photo / PDF (Optional)</span>
                                <input type="file" name="attachment" accept="image/jpeg,image/png,image/webp,application/pdf" class="hidden" onchange="if(this.files[0]) document.getElementById('replyAttachmentName').textContent = this.files[0].name;">
                            </label>
                            <span id="replyAttachmentName" class="text-xs text-indigo-600 font-semibold ml-2"></span>
                            @error('attachment')
                                <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs sm:text-sm shadow-md shadow-indigo-600/25 transition cursor-pointer">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Send Message</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

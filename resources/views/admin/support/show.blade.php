@extends('admin.layouts.admin')

@section('title', 'Ticket #' . $ticket->ticket_number . ' - Support Workspace')

@section('content')
@php
    $adminUser = auth('admin')->user();
    $canManage = $adminUser?->isSuperAdmin() || $adminUser?->hasPermission('support.manage');
    $canReply = $adminUser?->isSuperAdmin() || $adminUser?->hasPermission('support.reply') || $adminUser?->hasPermission('support.manage');
    $statusBadge = $ticket->status_badge;
    $priorityBadge = $ticket->priority_badge;
    $partner = $ticket->order?->deliveryPartner;
@endphp

<div class="space-y-6">
    <!-- Header & Navigation Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('admin.support.index') }}" class="hover:text-white transition flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Support Desk</span>
                </a>
                <span>/</span>
                <span class="text-indigo-400 font-mono">#{{ $ticket->ticket_number }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-xl md:text-2xl font-black text-white tracking-tight">
                    {{ $ticket->subject }}
                </h1>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }}">
                    <i class="{{ $statusBadge['icon'] }} text-[10px]"></i>
                    <span>{{ $statusBadge['label'] }}</span>
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $priorityBadge['bg'] }} {{ $priorityBadge['text'] }}">
                    <span>{{ $priorityBadge['label'] }} Priority</span>
                </span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if($ticket->order)
                <a href="{{ route('admin.orders.show', $ticket->order->order_number) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold transition">
                    <i class="fa-solid fa-box text-indigo-400"></i>
                    <span>Order #{{ $ticket->order->order_number }}</span>
                </a>
            @endif
            <a href="{{ route('admin.support.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                <i class="fa-solid fa-list"></i>
                <span>All Tickets</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-950/60 border border-emerald-800 text-emerald-300 rounded-2xl text-xs font-semibold flex items-center gap-2 shadow-xs">
            <i class="fa-solid fa-circle-check text-emerald-400 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left 8 Cols: Chat Timeline & Reply Form -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- Conversation History -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-xl overflow-hidden flex flex-col">
                <!-- Chat Stream Header -->
                <div class="px-6 py-4 bg-slate-950/50 border-b border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-comments text-indigo-400"></i>
                        <span class="text-xs font-bold text-white uppercase tracking-wider">Ticket Thread</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">
                            {{ $ticket->messages->count() }} records
                        </span>
                    </div>

                    <div class="text-[11px] text-slate-400 flex items-center gap-3">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-indigo-400"></span> Customer</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> Staff</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Staff Note</span>
                    </div>
                </div>

                <!-- Messages Timeline -->
                <div class="p-6 space-y-5 max-h-[650px] overflow-y-auto bg-slate-950/20">
                    @forelse($ticket->messages as $msg)
                        @php
                            $isUser = $msg->sender_type === \App\Models\SupportMessage::SENDER_USER;
                            $isInternal = $msg->is_internal;
                        @endphp

                        @if($isInternal)
                            <!-- Internal Staff Note Card -->
                            <div class="p-4 rounded-2xl bg-amber-950/30 border border-amber-500/40 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-black uppercase tracking-wider flex items-center gap-1">
                                            <i class="fa-solid fa-lock text-[9px]"></i>
                                            <span>Internal Staff Note</span>
                                        </span>
                                        <span class="font-bold text-amber-200">{{ $msg->admin?->name ?? 'Staff Admin' }}</span>
                                    </div>
                                    <span class="text-[11px] text-amber-400/80 font-mono">{{ $msg->created_at->format('d M, h:i A') }}</span>
                                </div>

                                <div class="text-xs text-amber-100/90 whitespace-pre-line leading-relaxed pl-1">
                                    {{ $msg->message }}
                                </div>

                                @if($msg->attachment)
                                    <div class="mt-2 pt-2 border-t border-amber-500/20">
                                        <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-amber-300 hover:text-amber-200 underline font-semibold">
                                            <i class="fa-solid fa-paperclip"></i>
                                            <span>View Attached Document</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @else
                            <!-- Public Customer/Agent Chat Bubble -->
                            <div class="flex {{ $isUser ? 'justify-start' : 'justify-end' }}">
                                <div class="max-w-xl flex gap-3 {{ $isUser ? 'flex-row' : 'flex-row-reverse' }}">
                                    <!-- Avatar -->
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 {{ $isUser ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'bg-emerald-600/20 text-emerald-400 border border-emerald-500/30' }}">
                                        @if($isUser)
                                            <i class="fa-solid fa-user"></i>
                                        @else
                                            <i class="fa-solid fa-headset"></i>
                                        @endif
                                    </div>

                                    <!-- Bubble Content -->
                                    <div class="space-y-1 {{ $isUser ? 'items-start' : 'items-end' }}">
                                        <div class="flex items-center gap-2 text-[11px] {{ $isUser ? 'justify-start text-slate-400' : 'justify-end text-slate-400' }}">
                                            <span class="font-bold {{ $isUser ? 'text-indigo-400' : 'text-emerald-400' }}">
                                                {{ $isUser ? ($ticket->name . ' (Customer)') : ($msg->admin?->name . ' (Support)') }}
                                            </span>
                                            <span>&bull;</span>
                                            <span class="font-mono">{{ $msg->created_at->format('d M, h:i A') }}</span>
                                        </div>

                                        <div class="p-4 rounded-2xl text-xs leading-relaxed whitespace-pre-line shadow-md {{ $isUser ? 'bg-slate-800 text-slate-200 border border-slate-700/80 rounded-tl-none' : 'bg-indigo-600 text-white rounded-tr-none' }}">
                                            {{ $msg->message }}

                                            <!-- Attachment Preview -->
                                            @if($msg->attachment)
                                                @php
                                                    $ext = pathinfo($msg->attachment, PATHINFO_EXTENSION);
                                                    $isImg = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp']);
                                                @endphp
                                                <div class="mt-3 pt-2.5 border-t {{ $isUser ? 'border-slate-700' : 'border-indigo-500/40' }}">
                                                    @if($isImg)
                                                        <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank" class="block group relative rounded-xl overflow-hidden max-w-xs border border-slate-700/80">
                                                            <img src="{{ asset('storage/' . $msg->attachment) }}" alt="Ticket Attachment" class="w-full max-h-40 object-cover group-hover:scale-105 transition">
                                                            <span class="absolute bottom-1 right-1 bg-black/70 text-white text-[10px] px-1.5 py-0.5 rounded">View full</span>
                                                        </a>
                                                    @else
                                                        <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold {{ $isUser ? 'bg-slate-900 text-indigo-400' : 'bg-indigo-700 text-white' }} transition">
                                                            <i class="fa-solid fa-file-pdf"></i>
                                                            <span>Attachment ({{ strtoupper($ext) }})</span>
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="text-center py-10 text-slate-500 text-xs">
                            No messages in this ticket yet.
                        </div>
                    @endforelse
                </div>

                <!-- Admin Reply & Internal Note Form -->
                @if($canReply)
                    <div class="p-6 bg-slate-950/60 border-t border-slate-800">
                        <form action="{{ route('admin.support.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label for="adminMessage" class="text-xs font-bold text-slate-300">
                                        Post a Response or Internal Note
                                    </label>

                                    <!-- Internal Note Checkbox Toggle -->
                                    <label class="inline-flex items-center gap-2 cursor-pointer bg-slate-800 px-3 py-1 rounded-xl border border-slate-700 hover:border-amber-500/50 transition">
                                        <input type="checkbox" name="is_internal" value="1" id="internalNoteCheck" class="rounded bg-slate-900 border-slate-700 text-amber-500 focus:ring-amber-500">
                                        <span class="text-xs font-bold text-amber-400 flex items-center gap-1">
                                            <i class="fa-solid fa-lock text-[10px]"></i>
                                            <span>Internal Staff Note (Invisible to User)</span>
                                        </span>
                                    </label>
                                </div>

                                <textarea id="adminMessage" name="message" rows="3" required placeholder="Type reply to customer, or check 'Internal Staff Note' above to record private investigation notes..." class="w-full bg-slate-900 border border-slate-700 rounded-2xl p-3.5 text-xs sm:text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none transition placeholder-slate-500"></textarea>
                                @error('message')
                                    <p class="text-xs text-rose-400 mt-1 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-700 hover:bg-slate-800 text-slate-400 hover:text-slate-200 text-xs font-semibold cursor-pointer transition">
                                        <i class="fa-solid fa-paperclip"></i>
                                        <span>Attach File</span>
                                        <input type="file" name="attachment" accept="image/jpeg,image/png,image/webp,application/pdf" class="hidden" onchange="if(this.files[0]) document.getElementById('adminAttachmentName').textContent = this.files[0].name;">
                                    </label>
                                    <span id="adminAttachmentName" class="text-xs text-indigo-400 font-semibold truncate max-w-xs"></span>

                                    <!-- Status update with reply -->
                                    <select name="new_status" class="bg-slate-900 border border-slate-700 rounded-xl px-2.5 py-1.5 text-xs text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="">Status: Keep Current ({{ ucfirst($ticket->status) }})</option>
                                        <option value="in_progress">Mark as In Progress</option>
                                        <option value="resolved">Mark as Resolved</option>
                                        <option value="closed">Mark as Closed</option>
                                    </select>
                                </div>

                                <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition cursor-pointer shrink-0">
                                    <i class="fa-solid fa-paper-plane"></i>
                                    <span>Send / Post</span>
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>

        </div>

        <!-- Right 4 Cols: Partner Call Card, Order Card & Ticket Controls -->
        <div class="lg:col-span-4 space-y-6">

            <!-- 1. DELIVERY PARTNER CALL & ESCALATION CARD -->
            @if($ticket->order && $partner)
                <div class="bg-slate-900 border border-emerald-500/30 rounded-3xl p-6 shadow-xl space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-base border border-emerald-500/30">
                                <i class="fa-solid fa-person-biking"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-white">Delivery Partner Escalation</h3>
                                <p class="text-[11px] text-slate-400">Order delivery rider on assignment</p>
                            </div>
                        </div>

                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider {{ $partner->status === 'approved' ? 'bg-emerald-950/60 text-emerald-400 border border-emerald-800' : 'bg-slate-800 text-slate-400' }}">
                            {{ $partner->is_online ? '● Online' : '○ Offline' }}
                        </span>
                    </div>

                    <!-- Rider Info Summary -->
                    <div class="p-3.5 rounded-2xl bg-slate-950/40 border border-slate-800 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Rider Name:</span>
                            <span class="font-bold text-white">{{ $partner->name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Vehicle:</span>
                            <span class="font-semibold text-slate-200">{{ ucfirst($partner->vehicle_type ?? 'Bike') }} &bull; {{ $partner->vehicle_number ?? 'Fleet' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Phone Number:</span>
                            <span class="font-mono font-bold text-emerald-400">{{ $partner->phone }}</span>
                        </div>
                        @if($ticket->partner_contacted)
                            <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-emerald-400">
                                <span class="flex items-center gap-1 font-semibold">
                                    <i class="fa-solid fa-phone"></i> Contact Status:
                                </span>
                                <span class="font-bold">Contacted by Staff</span>
                            </div>
                        @endif
                    </div>

                    <!-- Direct Call Action Buttons -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @if($partner->phone)
                            <a href="tel:{{ $partner->phone }}" class="inline-flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm shadow-emerald-600/30 transition cursor-pointer">
                                <i class="fa-solid fa-phone"></i>
                                <span>Call Partner</span>
                            </a>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $partner->phone) }}?text=Hello%20{{ urlencode($partner->name) }}%20regarding%20Order%20{{ $ticket->order->order_number }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-emerald-400 border border-slate-700 font-bold text-xs transition cursor-pointer">
                                <i class="fa-brands fa-whatsapp"></i>
                                <span>WhatsApp</span>
                            </a>
                        @endif
                    </div>

                    <!-- Log Partner Call Form -->
                    <div class="pt-3 border-t border-slate-800 space-y-2.5">
                        <label class="block text-xs font-bold text-slate-300">
                            Log Partner Call &amp; Resolution Notes
                        </label>
                        <form action="{{ route('admin.support.call-partner', $ticket->id) }}" method="POST" class="space-y-2">
                            @csrf
                            <textarea name="call_notes" rows="2" required placeholder="e.g. Called rider Rahul; he was held up by traffic, guaranteed delivery in 10 mins..." class="w-full bg-slate-950/70 border border-slate-700 rounded-xl p-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"></textarea>
                            @error('call_notes')
                                <p class="text-xs text-rose-400 font-semibold">{{ $message }}</p>
                            @enderror
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-slate-800 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 text-xs font-bold transition cursor-pointer">
                                <i class="fa-solid fa-clipboard-check"></i>
                                <span>Log Driver Call Into Ticket</span>
                            </button>
                        </form>
                    </div>

                    <!-- Past Partner Call Log if recorded -->
                    @if($ticket->partner_contact_notes)
                        <div class="pt-2 border-t border-slate-800">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Call History Log</span>
                            <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-[11px] text-slate-300 font-mono whitespace-pre-line leading-relaxed max-h-32 overflow-y-auto">
                                {{ $ticket->partner_contact_notes }}
                            </div>
                        </div>
                    @endif
                </div>
            @elseif($ticket->order)
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 text-xs text-slate-400 space-y-2">
                    <div class="flex items-center gap-2 text-slate-300 font-bold">
                        <i class="fa-solid fa-person-biking text-slate-500"></i>
                        <span>No Delivery Partner Assigned</span>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        This order does not have an assigned delivery partner yet. You can assign a rider from the order management screen.
                    </p>
                    <a href="{{ route('admin.orders.show', $ticket->order->order_number) }}" class="inline-flex items-center gap-1.5 text-indigo-400 hover:text-indigo-300 font-bold text-xs pt-1">
                        <span>Go to Order Details &rarr;</span>
                    </a>
                </div>
            @endif

            <!-- 2. TICKET STATUS & ASSIGNMENT CONTROLS -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                <h3 class="text-sm font-bold text-white pb-3 border-b border-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-indigo-400"></i>
                    <span>Ticket Management</span>
                </h3>

                @if($canManage)
                    <form action="{{ route('admin.support.status', $ticket->id) }}" method="POST" class="space-y-3.5">
                        @csrf

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1.5">Ticket Status</label>
                            <select name="status" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                @foreach(\App\Models\SupportTicket::STATUSES as $st)
                                    <option value="{{ $st }}" {{ $ticket->status === $st ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $st)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1.5">Assign Staff Member</label>
                            <select name="assigned_admin_id" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Unassigned --</option>
                                @foreach($allAdmins as $adm)
                                    <option value="{{ $adm->id }}" {{ $ticket->assigned_admin_id === $adm->id ? 'selected' : '' }}>
                                        {{ $adm->name }} ({{ $adm->roles->pluck('name')->join(', ') ?: 'Staff' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs border border-slate-700 transition cursor-pointer">
                            <i class="fa-solid fa-floppy-disk text-indigo-400"></i>
                            <span>Update Status &amp; Assignment</span>
                        </button>
                    </form>
                @else
                    <div class="text-xs text-slate-400 space-y-2">
                        <div class="flex items-center justify-between">
                            <span>Status:</span>
                            <span class="font-bold text-white">{{ ucfirst($ticket->status) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Assigned To:</span>
                            <span class="font-bold text-white">{{ $ticket->assignedAdmin?->name ?? 'Unassigned' }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- 3. CUSTOMER INFORMATION CARD -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-3 text-xs">
                <h3 class="text-sm font-bold text-white pb-3 border-b border-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-user text-indigo-400"></i>
                    <span>Customer Details</span>
                </h3>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Full Name:</span>
                        <span class="font-bold text-white">{{ $ticket->name }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Email:</span>
                        <a href="mailto:{{ $ticket->email }}" class="text-indigo-400 hover:underline">{{ $ticket->email }}</a>
                    </div>
                    @if($ticket->phone)
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Phone:</span>
                            <a href="tel:{{ $ticket->phone }}" class="text-emerald-400 font-bold hover:underline">{{ $ticket->phone }}</a>
                        </div>
                    @endif
                    @if($ticket->user)
                        <div class="flex items-center justify-between pt-2 border-t border-slate-800">
                            <span class="text-slate-400">Registered User:</span>
                            <span class="font-bold text-indigo-400">Yes (#{{ $ticket->user->id }})</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 4. LINKED ORDER SUMMARY (IF ANY) -->
            @if($ticket->order)
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-3 text-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <h3 class="text-sm font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-box text-indigo-400"></i>
                            <span>Order Summary</span>
                        </h3>
                        <a href="{{ route('admin.orders.show', $ticket->order->order_number) }}" class="text-[11px] text-indigo-400 hover:underline font-bold">
                            View Order &rarr;
                        </a>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Order Number:</span>
                            <span class="font-mono font-bold text-white">#{{ $ticket->order->order_number }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Order Status:</span>
                            <span class="font-bold text-slate-200">{{ ucfirst($ticket->order->status) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Grand Total:</span>
                            <span class="font-bold text-white">₹{{ number_format($ticket->order->grand_total, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Items:</span>
                            <span class="font-semibold text-slate-300">{{ $ticket->order->items->count() }} item(s)</span>
                        </div>
                        @if($ticket->order->address)
                            <div class="pt-2 border-t border-slate-800">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold mb-1">Delivery Address:</span>
                                <p class="text-slate-300 leading-relaxed text-[11px]">
                                    {{ $ticket->order->address->address_line1 ?? '' }}, {{ $ticket->order->address->city ?? '' }} {{ $ticket->order->address->postal_code ?? '' }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>

    </div>
</div>
@endsection

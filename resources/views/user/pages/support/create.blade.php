@extends('user.layouts.app')

@section('title', 'Open Support Ticket | ' . config('app.name', 'Shopy'))

@section('content')
<div class="min-h-screen bg-slate-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Breadcrumb / Back -->
        <div class="flex items-center justify-between">
            <a href="{{ route('support.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Support Center</span>
            </a>
            <a href="tel:+916379644145" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1.5">
                <i class="fa-solid fa-phone"></i>
                <span>Call Helpline: +91 63796 44145</span>
            </a>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl p-6 sm:p-10 space-y-8">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100 text-xs font-semibold mb-2">
                    <i class="fa-solid fa-headset"></i>
                    <span>Assistance Request</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Open a Support Ticket</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Describe your issue in detail. Our customer operations team will investigate and reply directly via this ticket chat.
                </p>
            </div>

            <!-- Error Banner -->
            @if ($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>Please fix the following:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('support.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Linked Order Card (If specific order selected) -->
                @if($selectedOrder)
                    <input type="hidden" name="order_id" value="{{ $selectedOrder->id }}">
                    <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-sm shrink-0">
                                <i class="fa-solid fa-box"></i>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-indigo-700 block tracking-wider">Linked to Order</span>
                                <span class="text-sm font-mono font-black text-slate-900">#{{ $selectedOrder->order_number }}</span>
                                <span class="text-xs text-slate-500 block">Total: ₹{{ number_format($selectedOrder->grand_total, 2) }} &bull; {{ ucfirst(str_replace('_', ' ', $selectedOrder->status)) }}</span>
                            </div>
                        </div>
                        @if($selectedOrder->deliveryPartner)
                            <div class="text-xs sm:text-right bg-white px-3 py-1.5 rounded-xl border border-indigo-100">
                                <span class="text-[10px] text-slate-400 block font-semibold">Assigned Rider</span>
                                <span class="font-bold text-slate-800">{{ $selectedOrder->deliveryPartner->name }}</span>
                            </div>
                        @endif
                    </div>
                @elseif($userOrders->isNotEmpty())
                    <div>
                        <label for="order_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Is this related to a specific order? (Optional)
                        </label>
                        <select name="order_id" id="order_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">No specific order (General Inquiry)</option>
                            @foreach($userOrders as $uOrder)
                                <option value="{{ $uOrder->id }}" {{ old('order_id') == $uOrder->id ? 'selected' : '' }}>
                                    Order #{{ $uOrder->order_number }} (₹{{ number_format($uOrder->grand_total, 2) }} - {{ ucfirst(str_replace('_', ' ', $uOrder->status)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Contact Details -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Your Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required value="{{ old('name', auth()->user()?->name) }}"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Email Address <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" id="email" required value="{{ old('email', auth()->user()?->email) }}"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Phone Number
                        </label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone', auth()->user()?->phone) }}" placeholder="+91 98765 43210"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Issue Category & Subject -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="category" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Category / Topic <span class="text-rose-500">*</span>
                        </label>
                        <select name="category" id="category" required class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach(\App\Models\SupportTicket::CATEGORIES as $catKey => $catLabel)
                                <option value="{{ $catKey }}" {{ old('category', $selectedOrder ? 'delivery_delay' : '') === $catKey ? 'selected' : '' }}>
                                    {{ $catLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="subject" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Subject / Summary <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="subject" id="subject" required
                            value="{{ old('subject', $selectedOrder ? 'Help with Order #' . $selectedOrder->order_number : '') }}"
                            placeholder="e.g. Delivery rider is stuck or order has not arrived"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Message Body -->
                <div>
                    <label for="message" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Detailed Description <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="message" id="message" rows="5" required placeholder="Please provide any helpful context (e.g., rider location status, damaged item name, expected arrival time)..."
                        class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('message') }}</textarea>
                </div>

                <!-- Attachment Upload -->
                <div>
                    <label for="attachment" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Attachment Photo / Screenshot (Optional)
                    </label>
                    <input type="file" name="attachment" id="attachment" accept="image/jpeg,image/png,image/webp,application/pdf"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 file:cursor-pointer">
                    <span class="text-[11px] text-slate-400 mt-1 block">JPG, PNG, or PDF up to 5MB</span>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between gap-4 pt-4 border-t border-slate-100">
                    <a href="{{ route('support.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-lg shadow-indigo-600/30 transition cursor-pointer">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Submit Ticket &amp; Start Chat</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

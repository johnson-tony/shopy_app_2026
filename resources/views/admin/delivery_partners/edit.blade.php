@extends('admin.layouts.admin')

@section('title', 'Edit Partner: ' . $partner->name . ' — Fleet Management')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.delivery_partners.show', $partner) }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition mb-2">
                <i class="fa-solid fa-arrow-left"></i> Back to Partner Profile
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Edit Delivery Partner</h1>
                @php $badge = $partner->status_badge; @endphp
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $badge['bg'] }} {{ $badge['text'] }} border {{ $badge['border'] }}">
                    <i class="{{ $badge['icon'] }}"></i>
                    <span>{{ $badge['label'] }}</span>
                </span>
            </div>
            <p class="text-slate-400 text-sm mt-1">Update profile credentials, KYC documents, vehicle specs, and authorized shopping channels.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.delivery_partners.show', $partner) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition">
                <i class="fa-solid fa-id-card"></i>
                <span>View Profile</span>
            </a>
        </div>
    </div>

    <!-- Error Banner -->
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

    <form action="{{ route('admin.delivery_partners.update', $partner) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Section 1: Personal & Fleet Profile -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
            <div class="pb-4 border-b border-slate-800 flex items-center justify-between">
                <h2 class="text-base font-black text-white flex items-center gap-2">
                    <i class="fa-solid fa-user-circle text-indigo-400"></i> Partner Identity &amp; Status
                </h2>
                <span class="text-xs text-slate-500">ID: #{{ $partner->id }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Full Name <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $partner->name) }}" required
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none transition">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Email Address <span class="text-rose-400">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email', $partner->email) }}" required
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none transition">
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Phone Number <span class="text-rose-400">*</span>
                    </label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $partner->phone) }}" required
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none transition">
                </div>

                <!-- Change Password (Optional) -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        New Password <span class="text-slate-500 font-normal">(Leave blank to keep unchanged)</span>
                    </label>
                    <input type="password" name="password" id="password" placeholder="••••••••" minlength="6"
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm placeholder-slate-600 focus:outline-none transition">
                </div>

                <!-- Status Selection -->
                <div class="md:col-span-2">
                    <label for="status" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Partner Account Status <span class="text-rose-400">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-950 border border-slate-800 hover:border-indigo-500/50 cursor-pointer transition">
                            <input type="radio" name="status" value="active" {{ old('status', $partner->status) === 'active' ? 'checked' : '' }}
                                class="text-indigo-600 focus:ring-indigo-500 bg-slate-900 border-slate-700">
                            <div>
                                <span class="text-xs font-bold text-emerald-400 block">Active &amp; Approved</span>
                                <span class="text-[10px] text-slate-400">Can take delivery orders</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-950 border border-slate-800 hover:border-amber-500/50 cursor-pointer transition">
                            <input type="radio" name="status" value="pending_approval" {{ old('status', $partner->status) === 'pending_approval' ? 'checked' : '' }}
                                class="text-amber-500 focus:ring-amber-500 bg-slate-900 border-slate-700">
                            <div>
                                <span class="text-xs font-bold text-amber-400 block">Pending KYC Review</span>
                                <span class="text-[10px] text-slate-400">Under verification queue</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-950 border border-slate-800 hover:border-rose-500/50 cursor-pointer transition">
                            <input type="radio" name="status" value="rejected" {{ old('status', $partner->status) === 'rejected' ? 'checked' : '' }}
                                class="text-rose-500 focus:ring-rose-500 bg-slate-900 border-slate-700">
                            <div>
                                <span class="text-xs font-bold text-rose-400 block">Rejected KYC</span>
                                <span class="text-[10px] text-slate-400">Declined registration</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-950 border border-slate-800 hover:border-slate-700 cursor-pointer transition">
                            <input type="radio" name="status" value="suspended" {{ old('status', $partner->status) === 'suspended' ? 'checked' : '' }}
                                class="text-slate-500 focus:ring-slate-500 bg-slate-900 border-slate-700">
                            <div>
                                <span class="text-xs font-bold text-slate-400 block">Suspended</span>
                                <span class="text-[10px] text-slate-500">Temporarily paused</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Vehicle & Channel Authorizations -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
            <div class="pb-4 border-b border-slate-800">
                <h2 class="text-base font-black text-white flex items-center gap-2">
                    <i class="fa-solid fa-motorcycle text-indigo-400"></i> Vehicle &amp; Delivery Channel Authorizations
                </h2>
                <p class="text-xs text-slate-400 mt-1">Specify partner's vehicle and assign the shopping channels they are licensed to serve.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Vehicle Type -->
                <div>
                    <label for="vehicle_type" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Vehicle Type <span class="text-rose-400">*</span>
                    </label>
                    <select name="vehicle_type" id="vehicle_type" required
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none transition">
                        <option value="bike" {{ old('vehicle_type', $partner->vehicle_type) === 'bike' ? 'selected' : '' }}>Motorcycle / Motorbike</option>
                        <option value="scooter" {{ old('vehicle_type', $partner->vehicle_type) === 'scooter' ? 'selected' : '' }}>Scooter / Activa</option>
                        <option value="ev" {{ old('vehicle_type', $partner->vehicle_type) === 'ev' ? 'selected' : '' }}>Electric Vehicle (EV)</option>
                        <option value="bicycle" {{ old('vehicle_type', $partner->vehicle_type) === 'bicycle' ? 'selected' : '' }}>Bicycle</option>
                        <option value="van" {{ old('vehicle_type', $partner->vehicle_type) === 'van' ? 'selected' : '' }}>Delivery Van</option>
                        <option value="other" {{ old('vehicle_type', $partner->vehicle_type) === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Vehicle Registration Plate Number -->
                <div>
                    <label for="vehicle_number" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Vehicle Registration Number
                    </label>
                    <input type="text" name="vehicle_number" id="vehicle_number" value="{{ old('vehicle_number', $partner->vehicle_number) }}" placeholder="e.g. KA-01-AB-1234"
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white font-mono text-sm placeholder-slate-500 focus:outline-none transition">
                </div>

                <!-- Channel Authorizations -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Authorized Shopping Channels
                    </label>
                    <p class="text-xs text-slate-500 mb-3">Check all channels this partner is cleared to deliver for:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @php
                            $assignedModeIds = $partner->modes->pluck('id')->toArray();
                        @endphp
                        @foreach($allModes as $mode)
                            <label class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-slate-700 cursor-pointer transition">
                                <input type="checkbox" name="modes[]" value="{{ $mode->id }}"
                                    {{ in_array($mode->id, old('modes', $assignedModeIds)) ? 'checked' : '' }}
                                    class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4 bg-slate-900 border-slate-700">
                                <div>
                                    <span class="font-bold text-white text-xs block">{{ $mode->name }}</span>
                                    <span class="text-[10px] text-slate-400">{{ ucfirst($mode->slug) }} order fulfillments</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: KYC Verification Documents -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
            <div class="pb-4 border-b border-slate-800">
                <h2 class="text-base font-black text-white flex items-center gap-2">
                    <i class="fa-solid fa-id-card text-indigo-400"></i> KYC Onboarding Documents
                </h2>
                <p class="text-xs text-slate-400 mt-1">Review or update document numbers and replace scan photos if needed.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Driving License Number -->
                <div>
                    <label for="license_number" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Driving License (DL) Number
                    </label>
                    <input type="text" name="license_number" id="license_number" value="{{ old('license_number', $partner->license_number) }}" placeholder="e.g. DL-1420110012345"
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white font-mono text-sm placeholder-slate-500 focus:outline-none transition">
                </div>

                <!-- Driving License Photo Upload & Preview -->
                <div>
                    <label for="license_image" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Driving License Photo / Scan
                    </label>
                    @if($partner->license_image)
                        <div class="mb-2 flex items-center gap-3 p-2.5 rounded-xl bg-slate-950 border border-slate-800">
                            <img src="{{ asset('storage/' . $partner->license_image) }}" alt="Current License" class="w-12 h-10 object-cover rounded-lg border border-slate-700 shrink-0">
                            <div class="text-xs">
                                <span class="text-slate-300 font-semibold block">Current Document File</span>
                                <a href="{{ asset('storage/' . $partner->license_image) }}" target="_blank" class="text-indigo-400 hover:text-indigo-300 text-[11px] underline">
                                    View Full Document
                                </a>
                            </div>
                        </div>
                    @endif
                    <input type="file" name="license_image" id="license_image" accept="image/jpeg,image/png,image/webp"
                        class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/20 file:text-indigo-300 hover:file:bg-indigo-600/30 file:cursor-pointer">
                    <span class="text-[10px] text-slate-500 mt-1 block">Upload new file to replace (JPG, PNG, or WebP up to 4MB)</span>
                </div>

                <!-- ID Proof Type -->
                <div>
                    <label for="id_proof_type" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Identity Proof Type
                    </label>
                    <select name="id_proof_type" id="id_proof_type"
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none transition">
                        <option value="">Select ID Type...</option>
                        <option value="aadhaar" {{ old('id_proof_type', $partner->id_proof_type) === 'aadhaar' ? 'selected' : '' }}>Aadhaar Card</option>
                        <option value="pan" {{ old('id_proof_type', $partner->id_proof_type) === 'pan' ? 'selected' : '' }}>PAN Card</option>
                        <option value="voter_id" {{ old('id_proof_type', $partner->id_proof_type) === 'voter_id' ? 'selected' : '' }}>Voter ID Card</option>
                        <option value="passport" {{ old('id_proof_type', $partner->id_proof_type) === 'passport' ? 'selected' : '' }}>Passport</option>
                    </select>
                </div>

                <!-- ID Proof Number -->
                <div>
                    <label for="id_proof_number" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Identity Proof Number
                    </label>
                    <input type="text" name="id_proof_number" id="id_proof_number" value="{{ old('id_proof_number', $partner->id_proof_number) }}" placeholder="e.g. 1234-5678-9012"
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white font-mono text-sm placeholder-slate-500 focus:outline-none transition">
                </div>

                <!-- ID Proof Image Upload & Preview -->
                <div class="md:col-span-2">
                    <label for="id_proof_image" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Identity Proof Document Photo / Scan
                    </label>
                    @if($partner->id_proof_image)
                        <div class="mb-2 flex items-center gap-3 p-2.5 rounded-xl bg-slate-950 border border-slate-800">
                            <img src="{{ asset('storage/' . $partner->id_proof_image) }}" alt="Current ID Proof" class="w-12 h-10 object-cover rounded-lg border border-slate-700 shrink-0">
                            <div class="text-xs">
                                <span class="text-slate-300 font-semibold block">Current Document File</span>
                                <a href="{{ asset('storage/' . $partner->id_proof_image) }}" target="_blank" class="text-indigo-400 hover:text-indigo-300 text-[11px] underline">
                                    View Full Document
                                </a>
                            </div>
                        </div>
                    @endif
                    <input type="file" name="id_proof_image" id="id_proof_image" accept="image/jpeg,image/png,image/webp"
                        class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/20 file:text-indigo-300 hover:file:bg-indigo-600/30 file:cursor-pointer">
                    <span class="text-[10px] text-slate-500 mt-1 block">Upload new file to replace (JPG, PNG, or WebP up to 4MB)</span>
                </div>
            </div>
        </div>

        <!-- Section 4: Banking & Payout Credentials -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
            <div class="pb-4 border-b border-slate-800">
                <h2 class="text-base font-black text-white flex items-center gap-2">
                    <i class="fa-solid fa-building-columns text-indigo-400"></i> Payout &amp; Banking Details
                </h2>
                <p class="text-xs text-slate-400 mt-1">Delivery earnings and return pickup allowances will be disbursed to this account.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Bank Account Number -->
                <div>
                    <label for="bank_account_number" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Bank Account Number
                    </label>
                    <input type="text" name="bank_account_number" id="bank_account_number" value="{{ old('bank_account_number', $partner->bank_account_number) }}" placeholder="e.g. 100234567890"
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white font-mono text-sm placeholder-slate-500 focus:outline-none transition">
                </div>

                <!-- Bank IFSC Code -->
                <div>
                    <label for="bank_ifsc" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Bank IFSC Code
                    </label>
                    <input type="text" name="bank_ifsc" id="bank_ifsc" value="{{ old('bank_ifsc', $partner->bank_ifsc) }}" placeholder="e.g. HDFC0001234"
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white font-mono text-sm placeholder-slate-500 focus:outline-none transition">
                </div>

                <!-- UPI ID -->
                <div>
                    <label for="upi_id" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        UPI ID / VPA
                    </label>
                    <input type="text" name="upi_id" id="upi_id" value="{{ old('upi_id', $partner->upi_id) }}" placeholder="e.g. partner@upi"
                        class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white font-mono text-sm placeholder-slate-500 focus:outline-none transition">
                </div>
            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="flex items-center justify-between gap-4 pt-2">
            <a href="{{ route('admin.delivery_partners.show', $partner) }}" class="px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition cursor-pointer">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Save Partner Changes</span>
            </button>
        </div>
    </form>
</div>
@endsection

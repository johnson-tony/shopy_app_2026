<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Join as Delivery Partner | {{ \App\Models\AdminSetting::siteName() }} Delivery</title>

    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background: radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.15) 0px, transparent 50%),
                        radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.15) 0px, transparent 50%),
                        #020617;
        }
    </style>
</head>
<body class="min-h-full flex flex-col items-center justify-center p-4 sm:p-6 font-sans antialiased relative">
    <div class="w-full max-w-2xl my-8">
        <!-- Logo & Branding -->
        <div class="text-center mb-6">
            @if(\App\Models\AdminSetting::hasCustomLogo())
                <img src="{{ \App\Models\AdminSetting::siteLogoUrl() }}" alt="{{ \App\Models\AdminSetting::siteName() }}" class="h-12 w-auto mx-auto object-contain mb-3">
            @else
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-600 text-white font-black text-2xl shadow-xl shadow-emerald-600/30 mb-3 ring-4 ring-emerald-500/20">
                    <i class="fa-solid fa-person-biking text-base"></i>
                </div>
            @endif
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                Join Our Delivery Fleet
            </h1>
            <p class="text-xs text-slate-400 mt-1 uppercase tracking-widest font-mono">
                Flexible Hours &bull; Instant Payouts &bull; Work Across Shopy &amp; Minutes
            </p>
        </div>

        <!-- Registration Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-2xl">
            @include('partner.components.alert')

            <form method="POST" action="{{ route('partner.register.submit') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Section 1: Basic Information -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-800 text-sm font-bold text-white">
                        <i class="fa-solid fa-user-circle text-emerald-400"></i>
                        <span>1. Personal Information</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Full Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Ramesh Kumar"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition @error('name') border-rose-500 @enderror">
                            @error('name')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Mobile Phone *</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="e.g. 9876543210"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition @error('phone') border-rose-500 @enderror">
                            @error('phone')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email Address *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="e.g. ramesh@example.com"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition @error('email') border-rose-500 @enderror">
                            @error('email')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Vehicle Type *</label>
                            <select name="vehicle_type" required class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition @error('vehicle_type') border-rose-500 @enderror">
                                <option value="bike" {{ old('vehicle_type') === 'bike' ? 'selected' : '' }}>Motorcycle / Bike</option>
                                <option value="scooter" {{ old('vehicle_type') === 'scooter' ? 'selected' : '' }}>Scooter / Scooty</option>
                                <option value="ev" {{ old('vehicle_type') === 'ev' ? 'selected' : '' }}>Electric Scooter (EV)</option>
                                <option value="bicycle" {{ old('vehicle_type') === 'bicycle' ? 'selected' : '' }}>Bicycle (Local Deliveries)</option>
                                <option value="van" {{ old('vehicle_type') === 'van' ? 'selected' : '' }}>Van / Mini Cargo</option>
                            </select>
                            @error('vehicle_type')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Vehicle Plate Number (Optional)</label>
                            <input type="text" name="vehicle_number" value="{{ old('vehicle_number') }}" placeholder="e.g. KA01AB1234"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Password *</label>
                            <input type="password" name="password" required placeholder="Minimum 8 characters"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition @error('password') border-rose-500 @enderror">
                            @error('password')<p class="text-rose-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Confirm Password *</label>
                            <input type="password" name="password_confirmation" required placeholder="Re-type your password"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Shopping Channels (Working Modes) -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-800 text-sm font-bold text-white">
                        <i class="fa-solid fa-layer-group text-emerald-400"></i>
                        <span>2. Delivery Channels You Want to Work For *</span>
                    </div>
                    <p class="text-xs text-slate-400">Select which types of deliveries you want to accept. You can choose multiple or all:</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                        @foreach($modes as $mode)
                            <label class="flex items-start gap-3 p-3.5 rounded-2xl bg-slate-950 border border-slate-800 hover:border-emerald-500/50 cursor-pointer transition">
                                <input type="checkbox" name="modes[]" value="{{ $mode->id }}"
                                    {{ in_array($mode->id, old('modes', $modes->pluck('id')->toArray())) ? 'checked' : '' }}
                                    class="mt-1 w-4 h-4 rounded border-slate-700 text-emerald-500 focus:ring-emerald-500">
                                <div>
                                    <span class="block text-xs font-bold text-white">{{ $mode->name }}</span>
                                    <span class="block text-[10px] text-slate-400 mt-0.5">
                                        @if($mode->slug === 'minutes')
                                            10-15 Min Grocery &amp; Essentials
                                        @elseif($mode->slug === 'food')
                                            Hot meals from restaurants
                                        @else
                                            E-commerce packages &amp; apparel
                                        @endif
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('modes')<p class="text-rose-400 text-xs">{{ $message }}</p>@enderror
                </div>

                <!-- Section 3: KYC Documents Verification -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-800 text-sm font-bold text-white">
                        <i class="fa-solid fa-id-card text-emerald-400"></i>
                        <span>3. KYC Document Verification</span>
                    </div>
                    <p class="text-xs text-slate-400">Upload clear photos of your documents. Our dispatch verification team will review them before activating your account.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Driving License -->
                        <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 space-y-2">
                            <label class="block text-xs font-bold text-slate-200 flex items-center gap-1.5">
                                <i class="fa-solid fa-address-card text-emerald-400"></i>
                                <span>Driving License</span>
                            </label>
                            <input type="text" name="license_number" value="{{ old('license_number') }}" placeholder="License Number (e.g. DL-1420110012345)"
                                class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 mb-1">Upload License Photo</label>
                                <input type="file" name="license_image" accept="image/*"
                                    class="w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-emerald-500/10 file:text-emerald-400 hover:file:bg-emerald-500/20 cursor-pointer">
                            </div>
                        </div>

                        <!-- ID Proof (Aadhaar / PAN) -->
                        <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 space-y-2">
                            <label class="block text-xs font-bold text-slate-200 flex items-center gap-1.5">
                                <i class="fa-solid fa-fingerprint text-emerald-400"></i>
                                <span>Identity Proof (Aadhaar / PAN)</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <select name="id_proof_type" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <option value="aadhaar" {{ old('id_proof_type') === 'aadhaar' ? 'selected' : '' }}>Aadhaar Card</option>
                                    <option value="pan" {{ old('id_proof_type') === 'pan' ? 'selected' : '' }}>PAN Card</option>
                                    <option value="voter_id" {{ old('id_proof_type') === 'voter_id' ? 'selected' : '' }}>Voter ID</option>
                                </select>
                                <input type="text" name="id_proof_number" value="{{ old('id_proof_number') }}" placeholder="Card Number"
                                    class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 mb-1">Upload ID Photo</label>
                                <input type="file" name="id_proof_image" accept="image/*"
                                    class="w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-emerald-500/10 file:text-emerald-400 hover:file:bg-emerald-500/20 cursor-pointer">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Payout Details (Optional) -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-800 text-sm font-bold text-white">
                        <i class="fa-solid fa-money-bill-transfer text-emerald-400"></i>
                        <span>4. Payout / Bank Account (For Weekly Earnings)</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Bank Account Number</label>
                            <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="Account Number"
                                class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Bank IFSC Code</label>
                            <input type="text" name="bank_ifsc" value="{{ old('bank_ifsc') }}" placeholder="e.g. SBIN0001234"
                                class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">UPI ID (Optional)</label>
                            <input type="text" name="upi_id" value="{{ old('upi_id') }}" placeholder="e.g. ramesh@okaxis"
                                class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>
                </div>

                <!-- Approval Notice Pill -->
                <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center gap-3 text-xs text-amber-300">
                    <i class="fa-solid fa-shield-check text-amber-400 text-base shrink-0"></i>
                    <span>Once submitted, your account will enter <strong>Pending Verification</strong> status. You will receive an SMS/email notification as soon as our operations team approves your application.</span>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3.5 px-4 rounded-xl text-white font-bold bg-emerald-600 hover:bg-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition shadow-lg shadow-emerald-600/30 text-sm cursor-pointer flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    <span>Submit Delivery Partner Application</span>
                </button>
            </form>

            <div class="pt-4 border-t border-slate-800 text-center text-xs text-slate-400">
                <span>Already an onboarded delivery partner?</span>
                <a href="{{ route('partner.login') }}" class="text-emerald-400 hover:text-emerald-300 font-bold transition ml-1">
                    Log In Here &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- jQuery & Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    @include('components.toastr')
</body>
</html>

@extends('admin.layouts.admin')

@section('title', 'Site & System Settings')

@section('content')
<div class="space-y-8 max-w-3xl">
    <!-- Page Header -->
    <div class="pb-5 border-b border-slate-800 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Site &amp; System Settings</h1>
            <p class="text-xs text-slate-400 mt-1">Manage global brand identity, site logo, storefront theme, and delivery/partner settings.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
            <i class="fa-solid fa-sliders text-[11px]"></i> Global Config
        </span>
    </div>

    <!-- Main Settings Form -->
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Card 1: Brand Identity & Logo -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-store text-base"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">Brand Identity &amp; Logo</h2>
                    <p class="text-xs text-slate-400">Customizes the site name and official logo displayed across storefront and admin areas.</p>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Site Name Input -->
                <div>
                    <label for="site_name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Site / Brand Name
                    </label>
                    <div class="relative">
                        <input type="text"
                               id="site_name"
                               name="site_name"
                               value="{{ old('site_name', $siteName) }}"
                               placeholder="e.g. Shopy, MyStore 2026"
                               class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('site_name') border-rose-500 @enderror">
                    </div>
                    @error('site_name')
                        <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-slate-500 mt-1.5">
                        Default fallback is <strong>Shopy</strong> if left empty. Updates storefront header, footer, titles, and admin portal.
                    </p>
                </div>

                <!-- Site Logo -->
                <div class="space-y-4">
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                        Site Logo
                    </label>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-6 p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80">
                        <!-- Current Logo Preview -->
                        <div class="flex flex-col items-center gap-2 shrink-0">
                            <div class="w-32 h-16 rounded-xl bg-white/95 border border-slate-700 flex items-center justify-center p-2 shadow-sm overflow-hidden">
                                <img id="logoPreview"
                                     src="{{ $siteLogoUrl }}"
                                     alt="{{ $siteName }}"
                                     class="max-h-full max-w-full object-contain">
                            </div>
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $hasCustomLogo ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $hasCustomLogo ? 'bg-emerald-400' : 'bg-slate-400' }}"></span>
                                {{ $hasCustomLogo ? 'Custom Uploaded' : 'Default Logo' }}
                            </span>
                        </div>

                        <!-- Upload New Logo Controls -->
                        <div class="flex-1 space-y-3">
                            <div>
                                <input type="file"
                                       id="site_logo"
                                       name="site_logo"
                                       accept="image/png,image/jpeg,image/svg+xml,image/webp"
                                       class="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 file:cursor-pointer transition cursor-pointer @error('site_logo') border-rose-500 @enderror">
                                @error('site_logo')
                                    <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>
                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                Recommended dimensions: <strong>200 &times; 50 px</strong> (transparent PNG or SVG). Max size: 2MB.
                            </p>

                            @if($hasCustomLogo)
                                <div class="pt-2 border-t border-slate-800 flex items-center gap-2">
                                    <label class="inline-flex items-center gap-2 text-xs text-rose-400 hover:text-rose-300 cursor-pointer">
                                        <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-700 bg-slate-900 text-rose-600 focus:ring-rose-500">
                                        <span>Reset to Default System Logo</span>
                                    </label>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Storefront Theme Permissions -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-palette text-base"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">Storefront Theme Permissions</h2>
                    <p class="text-xs text-slate-400">Control whether customers on the storefront can switch to Dark Mode.</p>
                </div>
            </div>

            <div>
                <label for="is_dark_mode" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    Dark Theme Permission
                </label>
                <select id="is_dark_mode" name="is_dark_mode"
                        class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition cursor-pointer">
                    <option value="0" {{ !$isDarkMode ? 'selected' : '' }}>No &mdash; Only Light Theme</option>
                    <option value="1" {{ $isDarkMode ? 'selected' : '' }}>Yes &mdash; Enable Dark Theme (User Can Change Both)</option>
                </select>
                <p class="text-[11px] text-slate-500 mt-1.5">
                    When disabled, the customer storefront will force Light Mode and hide the theme toggle pill in the header.
                </p>
            </div>
        </div>

        <!-- Card 3: Delivery & Partner Settings -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-truck-fast text-base"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">Delivery &amp; Partner System</h2>
                    <p class="text-xs text-slate-400">Enable or disable the delivery partner system globally and per shopping mode.</p>
                </div>
            </div>

            <div class="space-y-5">
                <!-- Global master switch -->
                <div class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80">
                    <div class="flex-1">
                        <label for="is_delivery_enabled" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                            Enable Delivery System
                        </label>
                        <p class="text-[11px] text-slate-500">
                            Master switch for the whole partner/delivery flow. When off, orders follow the manual admin status flow only.
                        </p>
                    </div>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="is_delivery_enabled" name="is_delivery_enabled" value="1" {{ $isDeliveryEnabled ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:bg-emerald-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5 relative"></div>
                    </label>
                </div>

                <!-- Per-mode toggles -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="flex items-center justify-between gap-3 p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80">
                        <div class="flex-1">
                            <label for="delivery_enabled_shopy" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                                Shopy (E-commerce)
                            </label>
                            <p class="text-[11px] text-slate-500">Milestone-based shipping flow.</p>
                        </div>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="delivery_enabled_shopy" name="delivery_enabled_shopy" value="1" {{ $deliveryEnabledShopy ? 'checked' : '' }}
                                   class="sr-only peer" @disabled(!$isDeliveryEnabled)>
                            <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5 relative"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between gap-3 p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80">
                        <div class="flex-1">
                            <label for="delivery_enabled_minutes" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                                Minutes (Quick Commerce)
                            </label>
                            <p class="text-[11px] text-slate-500">Nearest-partner GPS dispatch.</p>
                        </div>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="delivery_enabled_minutes" name="delivery_enabled_minutes" value="1" {{ $deliveryEnabledMinutes ? 'checked' : '' }}
                                   class="sr-only peer" @disabled(!$isDeliveryEnabled)>
                            <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:bg-sky-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5 relative"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between gap-3 p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80">
                        <div class="flex-1">
                            <label for="delivery_enabled_food" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                                Food Delivery
                            </label>
                            <p class="text-[11px] text-slate-500">Nearest-partner GPS dispatch.</p>
                        </div>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="delivery_enabled_food" name="delivery_enabled_food" value="1" {{ $deliveryEnabledFood ? 'checked' : '' }}
                                   class="sr-only peer" @disabled(!$isDeliveryEnabled)>
                            <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:bg-emerald-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5 relative"></div>
                        </label>
                    </div>
                </div>

                <p class="text-[11px] text-slate-500">
                    When the global switch is off, delivery is disabled for every mode. Partners and their panel remain accessible in the admin for management.
                </p>
            </div>
        </div>

        <!-- Card 4: Payment Methods & UPI Configuration -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-violet-500/10 text-violet-400 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-indian-rupee-sign text-base"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">Payment Methods &amp; UPI Configuration</h2>
                    <p class="text-xs text-slate-400">Configure accepted payment methods, store UPI VPA for PhonePe/GPay, and custom QR codes.</p>
                </div>
            </div>

            <!-- Payment Method Toggles -->
            <div class="space-y-4">
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                    Storefront Payment Toggles
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- COD -->
                    <div class="flex items-center justify-between gap-3 p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80">
                        <div class="flex-1">
                            <label for="is_cod_enabled" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                                Cash on Delivery
                            </label>
                            <p class="text-[11px] text-slate-500">Pay on doorstep.</p>
                        </div>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="is_cod_enabled" name="is_cod_enabled" value="1" {{ $isCodEnabled ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:bg-emerald-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5 relative"></div>
                        </label>
                    </div>

                    <!-- UPI -->
                    <div class="flex items-center justify-between gap-3 p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80">
                        <div class="flex-1">
                            <label for="is_upi_enabled" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                                UPI &amp; QR Pay
                            </label>
                            <p class="text-[11px] text-slate-500">PhonePe, GPay, Paytm.</p>
                        </div>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="is_upi_enabled" name="is_upi_enabled" value="1" {{ $isUpiEnabled ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:bg-violet-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5 relative"></div>
                        </label>
                    </div>

                    <!-- Card -->
                    <div class="flex items-center justify-between gap-3 p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80">
                        <div class="flex-1">
                            <label for="is_card_enabled" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                                Card Payment
                            </label>
                            <p class="text-[11px] text-slate-500">Credit / Debit Card.</p>
                        </div>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="is_card_enabled" name="is_card_enabled" value="1" {{ $isCardEnabled ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5 relative"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- UPI Details Inputs -->
            <div class="space-y-4 pt-2 border-t border-slate-800">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Store UPI VPA -->
                    <div>
                        <label for="upi_id" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                            Store UPI ID / VPA
                        </label>
                        <input type="text"
                               id="upi_id"
                               name="upi_id"
                               value="{{ old('upi_id', $upiId) }}"
                               placeholder="e.g. yourbusiness@okhdfcbank or 9876543210@paytm"
                               class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition @error('upi_id') border-rose-500 @enderror">
                        @error('upi_id')
                            <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                        @enderror
                        <p class="text-[11px] text-slate-500 mt-1.5">
                            Customer payments will be addressed to this UPI VPA.
                        </p>
                    </div>

                    <!-- Merchant Name -->
                    <div>
                        <label for="upi_merchant_name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                            Payee / Merchant Name
                        </label>
                        <input type="text"
                               id="upi_merchant_name"
                               name="upi_merchant_name"
                               value="{{ old('upi_merchant_name', $upiMerchantName) }}"
                               placeholder="e.g. Shopy Store"
                               class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition @error('upi_merchant_name') border-rose-500 @enderror">
                        @error('upi_merchant_name')
                            <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                        @enderror
                        <p class="text-[11px] text-slate-500 mt-1.5">
                            Displayed inside Google Pay, PhonePe, and Paytm when customer approves payment.
                        </p>
                    </div>
                </div>

                <!-- Custom UPI QR Code Upload (Optional) -->
                <div class="space-y-3 pt-3">
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                        Custom Standee / Merchant UPI QR Image (Optional)
                    </label>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-6 p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80">
                        @if($hasCustomUpiQr)
                            <div class="flex flex-col items-center gap-2 shrink-0">
                                <div class="w-24 h-24 rounded-xl bg-white p-2 border border-slate-700 flex items-center justify-center overflow-hidden">
                                    <img src="{{ $upiQrImageUrl }}" alt="Custom UPI QR" class="max-h-full max-w-full object-contain">
                                </div>
                                <label class="inline-flex items-center gap-1.5 text-[11px] text-rose-400 hover:text-rose-300 cursor-pointer">
                                    <input type="checkbox" name="remove_upi_qr" value="1" class="rounded border-slate-700 bg-slate-900 text-rose-600 focus:ring-rose-500">
                                    <span>Remove Custom QR</span>
                                </label>
                            </div>
                        @endif

                        <div class="flex-1 space-y-2">
                            <input type="file"
                                   id="upi_qr_image"
                                   name="upi_qr_image"
                                   accept="image/png,image/jpeg,image/webp"
                                   class="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-violet-600 file:text-white hover:file:bg-violet-500 file:cursor-pointer transition cursor-pointer @error('upi_qr_image') border-rose-500 @enderror">
                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                If left blank, the checkout page will automatically generate a dynamic QR code for each order with the exact order amount and order number pre-encoded.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-8 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-xl shadow-indigo-600/30 transition transform active:scale-95 cursor-pointer">
                <i class="fa-solid fa-floppy-disk text-xs"></i>
                Save All Settings
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const logoInput = document.getElementById('site_logo');
    const logoPreview = document.getElementById('logoPreview');

    if (logoInput && logoPreview) {
        logoInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    logoPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    const masterSwitch = document.getElementById('is_delivery_enabled');
    const modeToggleIds = ['delivery_enabled_shopy', 'delivery_enabled_minutes', 'delivery_enabled_food'];
    if (masterSwitch) {
        const updateDisabled = () => {
            modeToggleIds.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.disabled = !masterSwitch.checked;
            });
        };
        masterSwitch.addEventListener('change', updateDisabled);
        updateDisabled();
    }
});
</script>
@endsection

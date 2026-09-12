<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Services\CloudinaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(
        protected CloudinaryService $cloudinaryService
    ) {}

    /**
     * Display the admin settings management page.
     */
    public function index(): View
    {
        $setting = AdminSetting::instance();
        $isDarkMode = $setting->is_dark_mode;
        $siteName = AdminSetting::siteName();
        $siteLogoUrl = AdminSetting::siteLogoUrl();
        $hasCustomLogo = AdminSetting::hasCustomLogo();
        $isDeliveryEnabled = AdminSetting::isDeliveryEnabled();
        $deliveryEnabledShopy = AdminSetting::get('delivery_enabled_shopy', false);
        $deliveryEnabledMinutes = AdminSetting::get('delivery_enabled_minutes', true);
        $deliveryEnabledFood = AdminSetting::get('delivery_enabled_food', false);

        $upiId = AdminSetting::upiId();
        $upiMerchantName = AdminSetting::upiMerchantName();
        $upiQrImageUrl = AdminSetting::upiQrImageUrl();
        $hasCustomUpiQr = AdminSetting::hasCustomUpiQr();
        $isCodEnabled = AdminSetting::isCodEnabled();
        $isUpiEnabled = AdminSetting::isUpiEnabled();
        $isCardEnabled = AdminSetting::isCardEnabled();

        return view('admin.pages.settings', compact(
            'setting',
            'isDarkMode',
            'siteName',
            'siteLogoUrl',
            'hasCustomLogo',
            'isDeliveryEnabled',
            'deliveryEnabledShopy',
            'deliveryEnabledMinutes',
            'deliveryEnabledFood',
            'upiId',
            'upiMerchantName',
            'upiQrImageUrl',
            'hasCustomUpiQr',
            'isCodEnabled',
            'isUpiEnabled',
            'isCardEnabled',
        ));
    }

    /**
     * Update admin settings (Site name, Site logo, Dark Theme Permission, Delivery, Payment).
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'is_dark_mode' => ['nullable'],
            'site_name'    => ['nullable', 'string', 'max:100'],
            'site_logo'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
            'remove_logo'  => ['nullable'],
            'is_delivery_enabled'      => ['nullable'],
            'delivery_enabled_shopy'   => ['nullable'],
            'delivery_enabled_minutes' => ['nullable'],
            'delivery_enabled_food'    => ['nullable'],
            'upi_id'                   => ['nullable', 'string', 'max:100'],
            'upi_merchant_name'        => ['nullable', 'string', 'max:100'],
            'upi_qr_image'             => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_upi_qr'            => ['nullable'],
            'is_cod_enabled'           => ['nullable'],
            'is_upi_enabled'           => ['nullable'],
            'is_card_enabled'          => ['nullable'],
        ]);

        $setting = AdminSetting::instance();

        // 1. Update Dark Mode
        $isDarkMode = $request->boolean('is_dark_mode');
        $setting->is_dark_mode = $isDarkMode;

        // 2. Update Site Name (if empty, defaults to 'Shopy')
        $siteName = trim((string) $request->input('site_name', ''));
        $setting->site_name = $siteName !== '' ? $siteName : 'Shopy';

        // 3. Remove logo if requested
        if ($request->boolean('remove_logo')) {
            if ($setting->site_logo && !str_starts_with($setting->site_logo, 'http')) {
                Storage::disk('public')->delete($setting->site_logo);
            }
            $setting->site_logo = null;
        }

        // 4. Handle Site Logo Upload
        if ($request->hasFile('site_logo')) {
            try {
                if ($this->cloudinaryService->isConfigured()) {
                    $setting->site_logo = $this->cloudinaryService->uploadSiteLogo($request->file('site_logo'));
                } else {
                    $path = $request->file('site_logo')->store('site', 'public');
                    $setting->site_logo = $path;
                }
            } catch (\Throwable $e) {
                Log::error('Site Logo Upload Error: ' . $e->getMessage());
                // Fallback to local storage if Cloudinary upload throws
                $path = $request->file('site_logo')->store('site', 'public');
                $setting->site_logo = $path;
            }
        }

        // 5. Delivery settings
        if ($request->has('is_delivery_enabled') || $request->has('delivery_enabled_shopy')) {
            $setting->is_delivery_enabled    = $request->boolean('is_delivery_enabled');
            $setting->delivery_enabled_shopy = $request->boolean('delivery_enabled_shopy');
            $setting->delivery_enabled_minutes = $request->boolean('delivery_enabled_minutes');
            $setting->delivery_enabled_food  = $request->boolean('delivery_enabled_food');
        }

        // 6. Payment & UPI Settings
        $upiId = trim((string) $request->input('upi_id', ''));
        $setting->upi_id = $upiId !== '' ? $upiId : 'shopy@upi';

        $upiMerchantName = trim((string) $request->input('upi_merchant_name', ''));
        $setting->upi_merchant_name = $upiMerchantName !== '' ? $upiMerchantName : $setting->site_name;

        $setting->is_cod_enabled  = $request->boolean('is_cod_enabled');
        $setting->is_upi_enabled  = $request->boolean('is_upi_enabled');
        $setting->is_card_enabled = $request->boolean('is_card_enabled');

        // Remove custom UPI QR if requested
        if ($request->boolean('remove_upi_qr')) {
            if ($setting->upi_qr_image && !str_starts_with($setting->upi_qr_image, 'http')) {
                Storage::disk('public')->delete($setting->upi_qr_image);
            }
            $setting->upi_qr_image = null;
        }

        // Handle Custom UPI QR Upload
        if ($request->hasFile('upi_qr_image')) {
            try {
                $path = $request->file('upi_qr_image')->store('payments', 'public');
                $setting->upi_qr_image = $path;
            } catch (\Throwable $e) {
                Log::error('UPI QR Upload Error: ' . $e->getMessage());
            }
        }

        $setting->save();
        AdminSetting::clearCache();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Site settings updated successfully!');
    }
}

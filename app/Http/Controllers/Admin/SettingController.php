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

        return view('admin.pages.settings', compact('setting', 'isDarkMode', 'siteName', 'siteLogoUrl', 'hasCustomLogo'));
    }

    /**
     * Update admin settings (Site name, Site logo, Dark Theme Permission).
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'is_dark_mode' => ['nullable'],
            'site_name'    => ['nullable', 'string', 'max:100'],
            'site_logo'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
            'remove_logo'  => ['nullable'],
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

        $setting->save();
        AdminSetting::clearCache();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Site settings updated successfully!');
    }
}

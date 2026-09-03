<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Display the admin settings management page.
     */
    public function index(): View
    {
        $setting = AdminSetting::instance();
        $isDarkMode = $setting->is_dark_mode;

        return view('admin.pages.settings', compact('setting', 'isDarkMode'));
    }

    /**
     * Update admin dark mode boolean setting (Yes = true, No = false).
     */
    public function update(Request $request): RedirectResponse
    {
        $isDarkMode = $request->boolean('is_dark_mode');

        AdminSetting::setDarkMode($isDarkMode);

        $statusLabel = $isDarkMode ? 'Dark Theme (Yes)' : 'Light Theme (No)';

        return redirect()->route('admin.settings.index')
            ->with('success', "Portal appearance updated: {$statusLabel} is now active!");
    }
}

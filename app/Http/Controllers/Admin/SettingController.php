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
        $settings = [
            'theme' => AdminSetting::get('theme', 'light'),
            'site_name' => AdminSetting::get('site_name', config('app.name', 'Shopy 2026')),
            'support_email' => AdminSetting::get('support_email', 'support@shopy.test'),
            'support_phone' => AdminSetting::get('support_phone', '+91 63796 44145'),
        ];

        return view('admin.pages.settings', compact('settings'));
    }

    /**
     * Update admin settings in the database.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', 'in:light,dark'],
            'site_name' => ['required', 'string', 'max:100'],
            'support_email' => ['required', 'email', 'max:150'],
            'support_phone' => ['nullable', 'string', 'max:30'],
        ]);

        AdminSetting::set('theme', $validated['theme'], 'appearance');
        AdminSetting::set('site_name', $validated['site_name'], 'general');
        AdminSetting::set('support_email', $validated['support_email'], 'general');
        AdminSetting::set('support_phone', $validated['support_phone'] ?? '', 'general');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Admin settings updated successfully!');
    }
}

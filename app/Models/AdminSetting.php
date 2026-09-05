<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AdminSetting extends Model
{
    use HasFactory;

    protected $table = 'admin_settings';

    protected $fillable = [
        'is_dark_mode',
        'site_name',
        'site_logo',
    ];

    protected function casts(): array
    {
        return [
            'is_dark_mode' => 'boolean',
        ];
    }

    /**
     * Get or create the singleton setting record.
     */
    public static function instance(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            ['is_dark_mode' => false, 'site_name' => 'Shopy']
        );
    }

    /**
     * Get the active site name from DB, with fallback to config('app.name', 'Shopy').
     */
    public static function siteName(): string
    {
        try {
            return (string) Cache::remember('admin_setting_site_name', 3600, function () {
                $setting = static::first();
                return ($setting && !empty($setting->site_name)) ? $setting->site_name : config('app.name', 'Shopy');
            });
        } catch (\Throwable $e) {
            return config('app.name', 'Shopy');
        }
    }

    /**
     * Check if a custom site logo has been uploaded.
     */
    public static function hasCustomLogo(): bool
    {
        try {
            $setting = static::first();
            return !empty($setting?->site_logo);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get the active site logo URL, with fallback to the default static logo asset.
     */
    public static function siteLogoUrl(): string
    {
        try {
            return (string) Cache::remember('admin_setting_site_logo', 3600, function () {
                $setting = static::first();
                if ($setting && !empty($setting->site_logo)) {
                    // Full URL (Cloudinary or external)
                    if (str_starts_with($setting->site_logo, 'http://') || str_starts_with($setting->site_logo, 'https://')) {
                        return $setting->site_logo;
                    }
                    return asset('storage/' . $setting->site_logo);
                }
                return asset('images/logo/logo.png');
            });
        } catch (\Throwable $e) {
            return asset('images/logo/logo.png');
        }
    }

    /**
     * Check if dark mode is enabled (Yes = dark, No = light).
     */
    public static function isDarkMode(): bool
    {
        try {
            return (bool) Cache::remember('admin_setting_is_dark_mode', 3600, function () {
                $setting = static::first();
                return $setting ? (bool) $setting->is_dark_mode : false;
            });
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get the current theme string: 'dark' or 'light'.
     */
    public static function currentTheme(): string
    {
        return static::isDarkMode() ? 'dark' : 'light';
    }

    /**
     * Save the dark mode boolean setting (Yes = true, No = false).
     */
    public static function setDarkMode(bool $isDark): self
    {
        $setting = static::updateOrCreate(
            ['id' => 1],
            ['is_dark_mode' => $isDark]
        );

        static::clearCache();

        return $setting;
    }

    /**
     * Clear all cached settings.
     */
    public static function clearCache(): void
    {
        Cache::forget('admin_setting_is_dark_mode');
        Cache::forget('admin_setting_theme');
        Cache::forget('admin_setting_site_name');
        Cache::forget('admin_setting_site_logo');
    }

    /**
     * Backward-compatible get method.
     */
    public static function get(string $key = 'theme', mixed $default = 'light'): mixed
    {
        if ($key === 'theme') {
            return static::currentTheme();
        }
        if ($key === 'is_dark_mode') {
            return static::isDarkMode();
        }
        if ($key === 'site_name') {
            return static::siteName();
        }
        if ($key === 'site_logo') {
            return static::siteLogoUrl();
        }
        return $default;
    }

    /**
     * Backward-compatible set method.
     */
    public static function set(string $key, mixed $value, string $group = 'general'): self
    {
        if ($key === 'theme') {
            $isDark = ($value === 'dark' || $value === true || $value === 1 || $value === '1');
            return static::setDarkMode($isDark);
        }

        if ($key === 'is_dark_mode') {
            return static::setDarkMode((bool) $value);
        }

        if ($key === 'site_name') {
            $setting = static::updateOrCreate(['id' => 1], ['site_name' => (string) $value]);
            static::clearCache();
            return $setting;
        }

        if ($key === 'site_logo') {
            $setting = static::updateOrCreate(['id' => 1], ['site_logo' => (string) $value]);
            static::clearCache();
            return $setting;
        }

        return static::instance();
    }
}

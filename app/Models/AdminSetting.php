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
        return static::firstOrCreate(['id' => 1], ['is_dark_mode' => false]);
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

        Cache::forget('admin_setting_is_dark_mode');
        Cache::forget('admin_setting_theme');

        return $setting;
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

        return static::instance();
    }
}

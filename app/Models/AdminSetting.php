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
        'key',
        'value',
        'group',
    ];

    /**
     * Get a setting by key with optional default fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            return Cache::remember("admin_setting_{$key}", 3600, function () use ($key, $default) {
                $setting = static::where('key', $key)->first();
                return $setting ? $setting->value : $default;
            });
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Set/update a setting by key.
     */
    public static function set(string $key, mixed $value, string $group = 'general'): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget("admin_setting_{$key}");

        return $setting;
    }

    /**
     * Clear all cached settings.
     */
    public static function clearCache(): void
    {
        try {
            $keys = static::pluck('key');
            foreach ($keys as $key) {
                Cache::forget("admin_setting_{$key}");
            }
        } catch (\Throwable $e) {
            // silent catch
        }
    }
}

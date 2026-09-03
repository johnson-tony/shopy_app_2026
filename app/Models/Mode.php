<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Mode extends Model
{
    use HasFactory;

    protected $table = 'modes';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'status',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Scope query to only active modes.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Scope query ordered by sort_order ASC, then id ASC.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
    }

    /**
     * Get image URL (Cloudinary absolute URL or local storage URL).
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image)) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return asset('storage/' . $this->image);
    }

    /**
     * Generate a unique slug from mode name.
     */
    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        if (empty($baseSlug)) {
            $baseSlug = 'mode';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Check whether this mode has associated business records.
     * Extensible protection hook for future modules (categories, products, orders).
     */
    public function hasRelatedRecords(): bool
    {
        // Future categories check
        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'mode_id')) {
            if (DB::table('categories')->where('mode_id', $this->id)->exists()) {
                return true;
            }
        }

        // Future products check
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'mode_id')) {
            if (DB::table('products')->where('mode_id', $this->id)->exists()) {
                return true;
            }
        }

        // Future orders check
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'mode_id')) {
            if (DB::table('orders')->where('mode_id', $this->id)->exists()) {
                return true;
            }
        }

        return false;
    }
}

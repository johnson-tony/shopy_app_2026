<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Restaurant extends Model
{
    use HasFactory;

    protected $table = 'restaurants';

    protected $fillable = [
        'name',
        'slug',
        'image',
        'banner_image',
        'cuisine',
        'rating',
        'ratings_count',
        'delivery_time',
        'cost_for_two',
        'address',
        'city',
        'latitude',
        'longitude',
        'is_pure_veg',
        'is_featured',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'rating'        => 'decimal:1',
            'ratings_count' => 'integer',
            'cost_for_two'  => 'integer',
            'is_pure_veg'   => 'boolean',
            'is_featured'   => 'boolean',
            'status'        => 'boolean',
        ];
    }

    /**
     * Food items / dishes offered by this restaurant.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Active dishes.
     */
    public function activeProducts(): HasMany
    {
        return $this->hasMany(Product::class)->where('status', true);
    }

    /**
     * Active scope.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Featured scope.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Full image URL accessor.
     */
    public function getImageUrlAttribute(): string
    {
        if (empty($this->image)) {
            return 'https://placehold.co/400x300/f8fafc/334155?text=' . urlencode($this->name);
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        if (Storage::disk('public')->exists($this->image)) {
            return Storage::disk('public')->url($this->image);
        }

        return asset('storage/' . $this->image);
    }

    /**
     * Helper method for image URL.
     */
    public function imageUrl(): string
    {
        return $this->image_url;
    }

    /**
     * Full banner image URL accessor.
     */
    public function getBannerUrlAttribute(): string
    {
        if (empty($this->banner_image)) {
            return $this->image_url;
        }

        if (str_starts_with($this->banner_image, 'http://') || str_starts_with($this->banner_image, 'https://')) {
            return $this->banner_image;
        }

        if (Storage::disk('public')->exists($this->banner_image)) {
            return Storage::disk('public')->url($this->banner_image);
        }

        return asset('storage/' . $this->banner_image);
    }

    /**
     * Categories of food items available in this restaurant.
     */
    public function menuCategories()
    {
        return Category::whereIn('id', $this->products()->pluck('category_id')->unique())->get();
    }
}

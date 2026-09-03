<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'mode_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'short_description',
        'price',
        'sale_price',
        'stock',
        'image',
        'status',
        'featured',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'mode_id' => 'integer',
        'category_id' => 'integer',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock' => 'integer',
        'status' => 'boolean',
        'featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Shopping mode relationship.
     */
    public function mode(): BelongsTo
    {
        return $this->belongsTo(Mode::class);
    }

    /**
     * Category relationship.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope query to active products.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Scope query to featured products.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    /**
     * Scope query to in-stock products.
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Scope query to products of a specific mode (by ID or slug).
     */
    public function scopeForMode(Builder $query, int|string $mode): Builder
    {
        if (is_numeric($mode)) {
            return $query->where('mode_id', (int) $mode);
        }
        return $query->whereHas('mode', fn (Builder $q) => $q->where('slug', $mode));
    }

    /**
     * Scope query to products of a specific category.
     */
    public function scopeForCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Full image URL accessor (supports Cloudinary secure URLs and local storage).
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image)) {
            return null;
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
     * Determine if product currently has a valid discounted sale price.
     */
    public function getIsOnSaleAttribute(): bool
    {
        return !is_null($this->sale_price) && $this->sale_price > 0 && $this->sale_price < $this->price;
    }

    /**
     * Calculate discount percentage if product is on sale.
     */
    public function getDiscountPercentageAttribute(): ?int
    {
        if (!$this->is_on_sale || $this->price <= 0) {
            return null;
        }

        return (int) round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    /**
     * Stock status classification: in_stock, low_stock (<= 5), out_of_stock (<= 0).
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->stock <= 0) {
            return 'out_of_stock';
        }

        if ($this->stock <= 5) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * Generate a unique slug from product name.
     */
    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        if (empty($baseSlug)) {
            $baseSlug = 'product';
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
     * Check whether this product has associated order, cart, or review records.
     * Extensible protection hook for future checkout/order modules.
     */
    public function hasRelatedRecords(): bool
    {
        // Future order_items check
        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'product_id')) {
            if (DB::table('order_items')->where('product_id', $this->id)->exists()) {
                return true;
            }
        }

        // Future cart_items check
        if (Schema::hasTable('cart_items') && Schema::hasColumn('cart_items', 'product_id')) {
            if (DB::table('cart_items')->where('product_id', $this->id)->exists()) {
                return true;
            }
        }

        return false;
    }
}

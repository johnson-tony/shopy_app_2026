<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'restaurant_id',
        'name',
        'slug',
        'sku',
        'description',
        'short_description',
        'price',
        'sale_price',
        'stock',
        'delivery_time',
        'return_policy',
        'image',
        'images',
        'colors',
        'sizes',
        'addons',
        'is_veg',
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
        'restaurant_id' => 'integer',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock' => 'integer',
        'images' => 'array',
        'colors' => 'array',
        'sizes' => 'array',
        'addons' => 'array',
        'is_veg' => 'boolean',
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
     * Restaurant relationship (for food items).
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Category relationship.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Wishlists relationship.
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Check if product is wishlisted by a given user.
     */
    public function isWishlistedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return Wishlist::where('product_id', $this->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Estimated delivery timing for this product.
     * Falls back to the shopping mode default if not customized.
     */
    public function deliveryEstimate(): string
    {
        if (!empty($this->delivery_time)) {
            return $this->delivery_time;
        }

        $modeSlug = $this->mode?->slug ?? 'shopy';

        return match ($modeSlug) {
            'minutes' => '10-15 mins',
            'food'    => '30-45 mins',
            default   => '2-3 days',
        };
    }

    /**
     * FontAwesome icon class matching delivery type.
     */
    public function deliveryIcon(): string
    {
        $estimate = strtolower($this->deliveryEstimate());
        $modeSlug = $this->mode?->slug ?? 'shopy';

        if ($modeSlug === 'food') {
            return 'fa-solid fa-motorcycle';
        }

        if ($modeSlug === 'minutes' || str_contains($estimate, 'min')) {
            return 'fa-solid fa-bolt';
        }

        return 'fa-solid fa-truck-fast';
    }

    /**
     * Human-friendly return policy description text.
     */
    public function returnPolicyText(): string
    {
        $policy = $this->return_policy;

        // Smart fallback: If in Food or Minutes mode and not explicitly set to something else, default to non-returnable
        if (empty($policy)) {
            $modeSlug = $this->mode?->slug ?? 'shopy';
            if ($modeSlug === 'food' || $modeSlug === 'minutes') {
                return 'Non-Returnable';
            }
            return '7 Days Returnable';
        }

        return match ($policy) {
            'non_returnable'      => 'Non-Returnable',
            '7_days_replacement'  => '7 Days Replacement',
            '7_days_return'       => '7 Days Returnable',
            '10_days_return'      => '10 Days Return & Exchange',
            '30_days_return'      => '30 Days Returnable',
            default               => str_replace('_', ' ', ucwords($policy, '_')),
        };
    }

    /**
     * Icon for the return policy badge.
     */
    public function returnPolicyIcon(): string
    {
        return $this->isReturnable() 
            ? 'fa-solid fa-arrows-rotate text-emerald-500' 
            : 'fa-solid fa-ban text-rose-400';
    }

    /**
     * Whether this product can be returned/exchanged.
     */
    public function isReturnable(): bool
    {
        return ($this->return_policy ?? '') !== 'non_returnable';
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
     * Get all gallery images including the main cover image.
     *
     * @return array<int, string>
     */
    public function galleryImages(): array
    {
        $gallery = [];

        // Main cover image first
        $mainImg = $this->image_url;
        if ($mainImg) {
            $gallery[] = $mainImg;
        }

        // Additional gallery images
        if (is_array($this->images)) {
            foreach ($this->images as $img) {
                if (empty($img)) {
                    continue;
                }
                $url = filter_var($img, FILTER_VALIDATE_URL) ? $img : (Storage::disk('public')->exists($img) ? Storage::disk('public')->url($img) : asset('storage/' . $img));
                if (!in_array($url, $gallery)) {
                    $gallery[] = $url;
                }
            }
        }

        // If no images at all, fallback placeholder
        if (empty($gallery)) {
            $gallery[] = 'https://placehold.co/600x600/f1f5f9/475569?text=' . urlencode(Str::limit($this->name, 15));
        }

        return $gallery;
    }

    /**
     * Get normalized color options.
     *
     * @return array<int, array{name: string, hex: string}>
     */
    public function colorOptions(): array
    {
        if (!is_array($this->colors) || empty($this->colors)) {
            return [];
        }

        $list = [];
        foreach ($this->colors as $c) {
            if (is_array($c)) {
                $list[] = [
                    'name' => $c['name'] ?? 'Color',
                    'hex'  => $c['hex'] ?? '#3b82f6',
                ];
            } elseif (is_string($c)) {
                $colorName = trim($c);
                if ($colorName === '') {
                    continue;
                }
                $list[] = [
                    'name' => $colorName,
                    'hex'  => match (strtolower($colorName)) {
                        'black', 'space black', 'midnight' => '#0f172a',
                        'white', 'starlight' => '#f8fafc',
                        'silver', 'gray', 'grey' => '#94a3b8',
                        'blue', 'navy', 'deep blue' => '#2563eb',
                        'red', 'crimson' => '#dc2626',
                        'green', 'emerald' => '#059669',
                        'gold', 'desert titanium' => '#d97706',
                        'rose gold', 'pink' => '#ec4899',
                        'purple', 'violet' => '#7c3aed',
                        'yellow' => '#eab308',
                        'orange' => '#f97316',
                        default => '#64748b',
                    },
                ];
            }
        }

        return $list;
    }

    /**
     * Get size options.
     *
     * @return array<int, string>
     */
    public function sizeOptions(): array
    {
        if (!is_array($this->sizes) || empty($this->sizes)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', $this->sizes)));
    }

    /**
     * Check if product has customizable add-ons.
     */
    public function hasAddons(): bool
    {
        return is_array($this->addons) && !empty($this->addons);
    }

    /**
     * Get normalized add-on options / groups.
     *
     * @return array<int, array{group: string, type: string, options: array<int, array{name: string, price: float}>}>
     */
    public function addonsGroups(): array
    {
        if (!$this->hasAddons()) {
            return [];
        }

        $groups = [];
        foreach ($this->addons as $group) {
            if (!is_array($group)) {
                continue;
            }

            $rawItems = $group['items'] ?? $group['options'] ?? [];
            $options = [];
            foreach ($rawItems as $opt) {
                if (is_array($opt) && !empty($opt['name'])) {
                    $options[] = [
                        'name'  => (string) $opt['name'],
                        'price' => (float) ($opt['price'] ?? 0.00),
                    ];
                }
            }

            if (!empty($options)) {
                $groupTitle = $group['group_name'] ?? $group['group'] ?? 'Extras & Add-ons';
                $groups[] = [
                    'group'      => $groupTitle,
                    'group_name' => $groupTitle,
                    'type'       => $group['type'] ?? 'multiple', // 'multiple' (checkbox) or 'single' (radio)
                    'options'    => $options,
                    'items'      => $options,
                ];
            }
        }

        return $groups;
    }

    /**
     * Alias accessor for featured status.
     */
    public function getIsFeaturedAttribute(): bool
    {
        return (bool) $this->featured;
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

    /**
     * Cart items containing this product.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Order items containing this product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Product reviews.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Approved product reviews.
     */
    public function approvedReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->where('status', true);
    }

    /**
     * Average rating of product (1.0 to 5.0, default 4.8 if fresh demo, or calculated from reviews).
     */
    public function averageRating(): float
    {
        $avg = $this->approvedReviews()->avg('rating');

        if ($avg !== null && $avg > 0) {
            return round((float) $avg, 1);
        }

        // Fallback default rating for fresh products without reviews
        return 4.8;
    }

    /**
     * Total number of reviews.
     */
    public function reviewsCount(): int
    {
        return $this->approvedReviews()->count();
    }

    /**
     * Total rating count (calculated or fallback realistic count for demo).
     */
    public function ratingsCount(): int
    {
        $count = $this->reviewsCount();
        return $count > 0 ? $count : 1420;
    }

    /**
     * Star breakdown (counts and percentages for 5, 4, 3, 2, 1 stars).
     *
     * @return array<int, array{count: int, percentage: int}>
     */
    public function ratingBreakdown(): array
    {
        $reviews = $this->approvedReviews()->get();
        $total = $reviews->count();

        $breakdown = [
            5 => ['count' => 0, 'percentage' => 0],
            4 => ['count' => 0, 'percentage' => 0],
            3 => ['count' => 0, 'percentage' => 0],
            2 => ['count' => 0, 'percentage' => 0],
            1 => ['count' => 0, 'percentage' => 0],
        ];

        if ($total > 0) {
            foreach ($reviews as $rev) {
                $r = (int) $rev->rating;
                if (isset($breakdown[$r])) {
                    $breakdown[$r]['count']++;
                }
            }
            foreach ($breakdown as $star => $data) {
                $breakdown[$star]['percentage'] = (int) round(($data['count'] / $total) * 100);
            }
        } else {
            // Realistic sample baseline for display when fresh
            $breakdown = [
                5 => ['count' => 980, 'percentage' => 69],
                4 => ['count' => 310, 'percentage' => 22],
                3 => ['count' => 80,  'percentage' => 6],
                2 => ['count' => 30,  'percentage' => 2],
                1 => ['count' => 20,  'percentage' => 1],
            ];
        }

        return $breakdown;
    }

    /**
     * All customer review photo URLs across approved reviews.
     *
     * @return array<int, string>
     */
    public function customerPhotos(): array
    {
        $photos = [];
        $reviews = $this->approvedReviews()->whereNotNull('images')->get();

        foreach ($reviews as $review) {
            foreach ($review->imageUrls() as $url) {
                if (!in_array($url, $photos)) {
                    $photos[] = $url;
                }
            }
        }

        return $photos;
    }
}


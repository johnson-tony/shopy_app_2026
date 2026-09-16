<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_PENDING = 'pending';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_BLOCKED,
        self::STATUS_PENDING,
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
        'email_verified_at',
        'google_id',
        'google_avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function defaultAddress(): HasOne
    {
        return $this->hasOne(UserAddress::class)->where('is_default', true);
    }

    public function emailVerifications(): HasMany
    {
        return $this->hasMany(UserEmailVerification::class);
    }

    public function latestEmailVerification(): HasOne
    {
        return $this->hasOne(UserEmailVerification::class)->latestOfMany();
    }

    public function passwordResetOtps(): HasMany
    {
        return $this->hasMany(UserPasswordResetOtp::class);
    }

    public function latestPasswordResetOtp(): HasOne
    {
        return $this->hasOne(UserPasswordResetOtp::class)->latestOfMany();
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function wishlistProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'wishlists')->withTimestamps();
    }

    public function wishlistCount(?string $modeSlug = null): int
    {
        if ($modeSlug !== null && $modeSlug !== '' && $modeSlug !== 'all') {
            return $this->wishlistProducts()
                ->whereHas('mode', fn ($q) => $q->where('slug', $modeSlug))
                ->count();
        }

        return $this->wishlists()->count();
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function cartCount(?string $modeSlug = null): int
    {
        $query = CartItem::whereHas('cart', function ($q) use ($modeSlug) {
            $q->where('user_id', $this->id);
            if ($modeSlug !== null && $modeSlug !== '' && $modeSlug !== 'all') {
                $q->whereHas('mode', fn ($mq) => $mq->where('slug', $modeSlug));
            }
        });

        return (int) $query->sum('quantity');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->latest();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function hasPurchasedProduct(Product|int $product): bool
    {
        $productId = $product instanceof Product ? $product->id : $product;

        return OrderItem::where('product_id', $productId)
            ->whereHas('order', function ($q) {
                $q->where('user_id', $this->id)
                  ->where('status', '!=', Order::STATUS_CANCELLED);
            })
            ->exists();
    }

    public function getOrderIdForProduct(Product|int $product): ?int
    {
        $productId = $product instanceof Product ? $product->id : $product;

        return OrderItem::where('product_id', $productId)
            ->whereHas('order', function ($q) {
                $q->where('user_id', $this->id)
                  ->where('status', '!=', Order::STATUS_CANCELLED);
            })
            ->value('order_id');
    }

    public function hasDeliveredProduct(Product|int $product): bool
    {
        $productId = $product instanceof Product ? $product->id : $product;

        return OrderItem::where('product_id', $productId)
            ->whereHas('order', function ($q) {
                $q->where('user_id', $this->id)
                  ->where('status', Order::STATUS_DELIVERED);
            })
            ->exists();
    }

    public function getDeliveredOrderIdForProduct(Product|int $product): ?int
    {
        $productId = $product instanceof Product ? $product->id : $product;

        return OrderItem::where('product_id', $productId)
            ->whereHas('order', function ($q) {
                $q->where('user_id', $this->id)
                  ->where('status', Order::STATUS_DELIVERED);
            })
            ->latest('id')
            ->value('order_id');
    }
}

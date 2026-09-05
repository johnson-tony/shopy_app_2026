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

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user status is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if user status is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if user has verified their email address.
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Get all addresses for the user.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    /**
     * Get the default address for the user.
     */
    public function defaultAddress(): HasOne
    {
        return $this->hasOne(UserAddress::class)->where('is_default', true);
    }

    /**
     * Get all email verification records for the user.
     */
    public function emailVerifications(): HasMany
    {
        return $this->hasMany(UserEmailVerification::class);
    }

    /**
     * Get the latest email verification record for the user.
     */
    public function latestEmailVerification(): HasOne
    {
        return $this->hasOne(UserEmailVerification::class)->latestOfMany();
    }

    /**
     * Get all password reset OTP records for the user.
     */
    public function passwordResetOtps(): HasMany
    {
        return $this->hasMany(UserPasswordResetOtp::class);
    }

    /**
     * Get the latest password reset OTP record for the user.
     */
    public function latestPasswordResetOtp(): HasOne
    {
        return $this->hasOne(UserPasswordResetOtp::class)->latestOfMany();
    }

    /**
     * Get all wishlist records for the user.
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get all wishlisted products for the user.
     */
    public function wishlistProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'wishlists')->withTimestamps();
    }

    /**
     * Get the number of items in the user's wishlist, optionally filtered by shopping mode.
     */
    public function wishlistCount(?string $modeSlug = null): int
    {
        if ($modeSlug !== null && $modeSlug !== '' && $modeSlug !== 'all') {
            return $this->wishlistProducts()
                ->whereHas('mode', fn ($q) => $q->where('slug', $modeSlug))
                ->count();
        }

        return $this->wishlists()->count();
    }

    /**
     * Get all cart records for the user.
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Get the total item count in user's carts, optionally scoped by shopping mode.
     */
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
}


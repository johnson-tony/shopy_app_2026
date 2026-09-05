<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED = 'fixed';
    public const TYPE_FREE_DELIVERY = 'free_delivery';

    public const TYPES = [
        self::TYPE_PERCENTAGE,
        self::TYPE_FIXED,
        self::TYPE_FREE_DELIVERY,
    ];

    protected $table = 'coupons';

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'mode_id',
        'usage_limit',
        'usage_limit_per_user',
        'times_used',
        'starts_at',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'value'                => 'decimal:2',
            'min_order_amount'     => 'decimal:2',
            'max_discount_amount'  => 'decimal:2',
            'usage_limit'          => 'integer',
            'usage_limit_per_user' => 'integer',
            'times_used'           => 'integer',
            'starts_at'            => 'datetime',
            'expires_at'           => 'datetime',
            'status'               => 'boolean',
        ];
    }

    /**
     * Shopping mode relationship (null if valid across all modes).
     */
    public function mode(): BelongsTo
    {
        return $this->belongsTo(Mode::class);
    }

    /**
     * Usage logs for this coupon.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    // -------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------

    /**
     * Scope to active and non-expired coupons.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
    }

    /**
     * Scope coupons valid for a specific shopping mode (or universal).
     */
    public function scopeForMode(Builder $query, ?int $modeId): Builder
    {
        if ($modeId === null) {
            return $query;
        }

        return $query->where(function ($q) use ($modeId) {
            $q->whereNull('mode_id')->orWhere('mode_id', $modeId);
        });
    }

    // -------------------------------------------------------------
    // Validation & Calculations
    // -------------------------------------------------------------

    /**
     * Run all 7 validation checks on this coupon for a given cart and user.
     *
     * @return array{valid: bool, error: ?string}
     */
    public function validateForCart(Cart $cart, ?User $user = null): array
    {
        // 1. Active status
        if (!$this->status) {
            return ['valid' => false, 'error' => "The promo code '{$this->code}' is no longer active."];
        }

        // 2. Start date
        if ($this->starts_at && now()->lt($this->starts_at)) {
            return ['valid' => false, 'error' => "The promo code '{$this->code}' is not valid until {$this->starts_at->format('d M, Y')}."];
        }

        // 3. Expiry date
        if ($this->expires_at && now()->gt($this->expires_at)) {
            return ['valid' => false, 'error' => "The promo code '{$this->code}' expired on {$this->expires_at->format('d M, Y')}."];
        }

        // 4. Shopping Mode Restriction
        if ($this->mode_id && $cart->mode_id !== $this->mode_id) {
            $expectedMode = $this->mode?->name ?? 'another store';
            return ['valid' => false, 'error' => "Promo code '{$this->code}' can only be used in {$expectedMode} orders."];
        }

        // 5. Minimum Order Subtotal
        $subtotal = $cart->subtotal();
        if ($this->min_order_amount > 0 && $subtotal < $this->min_order_amount) {
            $remaining = number_format($this->min_order_amount - $subtotal, 2);
            return ['valid' => false, 'error' => "Add ₹{$remaining} more to your cart to use promo code '{$this->code}' (Min. ₹{$this->min_order_amount})."];
        }

        // 6. Overall Total Usage Limit
        if ($this->usage_limit !== null && $this->times_used >= $this->usage_limit) {
            return ['valid' => false, 'error' => "This promo code has reached its maximum redemption limit."];
        }

        // 7. Per-User Usage Limit (if user is authenticated)
        if ($user && $this->usage_limit_per_user > 0) {
            $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
            if ($userUsageCount >= $this->usage_limit_per_user) {
                return ['valid' => false, 'error' => "You have already used promo code '{$this->code}' the maximum allowed number of times."];
            }
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Compute the exact discount amount for a cart.
     */
    public function calculateDiscount(Cart $cart): float
    {
        $subtotal = $cart->subtotal();

        if ($subtotal <= 0) {
            return 0.00;
        }

        $discount = 0.00;

        switch ($this->type) {
            case self::TYPE_PERCENTAGE:
                $discount = round($subtotal * ((float) $this->value / 100), 2);
                if ($this->max_discount_amount !== null && $discount > (float) $this->max_discount_amount) {
                    $discount = (float) $this->max_discount_amount;
                }
                break;

            case self::TYPE_FIXED:
                $discount = min($subtotal, (float) $this->value);
                break;

            case self::TYPE_FREE_DELIVERY:
                $discount = (float) $cart->deliveryFee();
                break;
        }

        return round(min($subtotal, $discount), 2);
    }

    /**
     * Log a usage record for this coupon and increment times_used.
     */
    public function recordUsage(int $userId, float $discountAmount, ?int $orderId = null): CouponUsage
    {
        $usage = $this->usages()->create([
            'user_id'         => $userId,
            'order_id'        => $orderId,
            'discount_amount' => $discountAmount,
            'used_at'         => now(),
        ]);

        $this->increment('times_used');

        return $usage;
    }
}

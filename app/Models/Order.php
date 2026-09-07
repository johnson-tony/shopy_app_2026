<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY_FOR_DELIVERY = 'ready-for-delivery';
    public const STATUS_DELIVERY_ASSIGNED = 'delivery-assigned';
    public const STATUS_PICKED_UP = 'picked-up';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_OUT_FOR_DELIVERY = 'out-for-delivery';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_METHOD_COD = 'pay_on_delivery';
    public const PAYMENT_METHOD_MOCK_UPI = 'mock_upi';
    public const PAYMENT_METHOD_MOCK_CARD = 'mock_card';
    public const PAYMENT_METHOD_MOCK_NETBANKING = 'mock_netbanking';

    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_FAILED = 'failed';
    public const PAYMENT_STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'order_number',
        'user_id',
        'mode_id',
        'address_id',
        'delivery_partner_id',
        'status',
        'payment_method',
        'payment_status',
        'subtotal',
        'delivery_fee',
        'tax_amount',
        'discount_amount',
        'coupon_code',
        'grand_total',
        'notes',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
        'assigned_at',
        'ready_for_delivery_at',
        'picked_up_at',
        'out_for_delivery_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'assigned_at' => 'datetime',
            'ready_for_delivery_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'out_for_delivery_at' => 'datetime',
        ];
    }

    /**
     * Customer who placed the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Shopping mode (Shopy, Minutes, Food).
     */
    public function mode(): BelongsTo
    {
        return $this->belongsTo(Mode::class);
    }

    /**
     * Delivery address saved at the time of ordering.
     */
    public function userAddress(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }

    /**
     * Delivery partner assigned to fulfil this order.
     */
    public function deliveryPartner(): BelongsTo
    {
        return $this->belongsTo(DeliveryPartner::class, 'delivery_partner_id');
    }

    /**
     * Formatted shipping address line (delegates to the linked saved address).
     */
    public function getFormattedShippingAddressAttribute(): ?string
    {
        return $this->userAddress?->formatted_address;
    }

    /**
     * Items in this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Reviews associated with this order.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Generate unique human-readable order number.
     * e.g., SHP-202609-847291
     */
    public static function generateOrderNumber(): string
    {
        do {
            $number = 'SHP-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (static::where('order_number', $number)->exists());

        return $number;
    }

    /**
     * Check if order can be cancelled by user.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_CONFIRMED,
            self::STATUS_PROCESSING,
            self::STATUS_READY_FOR_DELIVERY,
        ]);
    }

    /**
     * All statuses this order can legally transition into from its current one.
     * This is the single source of truth used by admin updates and the partner flow.
     */
    public function canTransitionTo(string $status): bool
    {
        if ($status === $this->status) {
            return true;
        }

        $map = [
            self::STATUS_CONFIRMED        => [self::STATUS_PROCESSING, self::STATUS_READY_FOR_DELIVERY, self::STATUS_SHIPPED, self::STATUS_CANCELLED],
            self::STATUS_PROCESSING       => [self::STATUS_READY_FOR_DELIVERY, self::STATUS_SHIPPED, self::STATUS_CANCELLED],
            self::STATUS_READY_FOR_DELIVERY => [self::STATUS_DELIVERY_ASSIGNED, self::STATUS_SHIPPED, self::STATUS_CANCELLED],
            self::STATUS_DELIVERY_ASSIGNED => [self::STATUS_PICKED_UP, self::STATUS_READY_FOR_DELIVERY, self::STATUS_SHIPPED],
            self::STATUS_PICKED_UP        => [self::STATUS_OUT_FOR_DELIVERY, self::STATUS_DELIVERY_ASSIGNED],
            self::STATUS_OUT_FOR_DELIVERY => [self::STATUS_DELIVERED, self::STATUS_DELIVERY_ASSIGNED],
            self::STATUS_SHIPPED          => [self::STATUS_OUT_FOR_DELIVERY, self::STATUS_DELIVERED, self::STATUS_CANCELLED],
            self::STATUS_DELIVERED        => [],
            self::STATUS_CANCELLED        => [],
        ];

        return in_array($status, $map[$this->status] ?? [], true);
    }

    /**
     * Total number of product items in this order.
     */
    public function totalQuantity(): int
    {
        return (int) $this->items->sum('quantity');
    }

    /**
     * Human-friendly status badge styling.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            self::STATUS_DELIVERED => [
                'label' => 'Delivered',
                'bg' => 'bg-emerald-100 dark:bg-emerald-950/40',
                'text' => 'text-emerald-700 dark:text-emerald-400',
                'icon' => 'fa-solid fa-circle-check',
            ],
            self::STATUS_SHIPPED => [
                'label' => 'Shipped',
                'bg' => 'bg-blue-100 dark:bg-blue-950/40',
                'text' => 'text-blue-700 dark:text-blue-400',
                'icon' => 'fa-solid fa-truck-fast',
            ],
            self::STATUS_READY_FOR_DELIVERY => [
                'label' => 'Ready for Delivery',
                'bg' => 'bg-cyan-100 dark:bg-cyan-950/40',
                'text' => 'text-cyan-700 dark:text-cyan-400',
                'icon' => 'fa-solid fa-box-open',
            ],
            self::STATUS_DELIVERY_ASSIGNED => [
                'label' => 'Delivery Assigned',
                'bg' => 'bg-violet-100 dark:bg-violet-950/40',
                'text' => 'text-violet-700 dark:text-violet-400',
                'icon' => 'fa-solid fa-person-biking',
            ],
            self::STATUS_PICKED_UP => [
                'label' => 'Picked Up',
                'bg' => 'bg-sky-100 dark:bg-sky-950/40',
                'text' => 'text-sky-700 dark:text-sky-400',
                'icon' => 'fa-solid fa-basket-shopping',
            ],
            self::STATUS_OUT_FOR_DELIVERY => [
                'label' => 'Out for Delivery',
                'bg' => 'bg-orange-100 dark:bg-orange-950/40',
                'text' => 'text-orange-700 dark:text-orange-400',
                'icon' => 'fa-solid fa-motorcycle',
            ],
            self::STATUS_PROCESSING => [
                'label' => 'Processing',
                'bg' => 'bg-amber-100 dark:bg-amber-950/40',
                'text' => 'text-amber-700 dark:text-amber-400',
                'icon' => 'fa-solid fa-box-open',
            ],
            self::STATUS_CANCELLED => [
                'label' => 'Cancelled',
                'bg' => 'bg-rose-100 dark:bg-rose-950/40',
                'text' => 'text-rose-700 dark:text-rose-400',
                'icon' => 'fa-solid fa-circle-xmark',
            ],
            default => [
                'label' => 'Order Placed',
                'bg' => 'bg-indigo-100 dark:bg-indigo-950/40',
                'text' => 'text-indigo-700 dark:text-indigo-400',
                'icon' => 'fa-solid fa-bag-shopping',
            ],
        };
    }

    /**
     * Human-friendly payment method label.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            self::PAYMENT_METHOD_COD => 'Cash on Delivery (Pay at Doorstep)',
            self::PAYMENT_METHOD_MOCK_UPI => 'UPI (Doorstep / Test Mode)',
            self::PAYMENT_METHOD_MOCK_CARD => 'Credit / Debit Card (Test Mode)',
            self::PAYMENT_METHOD_MOCK_NETBANKING => 'Net Banking (Test Mode)',
            default => ucfirst(str_replace('_', ' ', $this->payment_method)),
        };
    }
}

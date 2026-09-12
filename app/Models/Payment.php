<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';

    public const GATEWAY_MANUAL = 'manual';
    public const GATEWAY_DIRECT_UPI = 'direct_upi';
    public const GATEWAY_SIMULATED_CARD = 'simulated_card';
    public const GATEWAY_RAZORPAY = 'razorpay';
    public const GATEWAY_PHONEPE = 'phonepe';
    public const GATEWAY_STRIPE = 'stripe';

    protected $fillable = [
        'order_id',
        'user_id',
        'payment_method',
        'payment_gateway',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'notes',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'amount'  => 'decimal:2',
            'payload' => 'array',
        ];
    }

    /**
     * Order linked to this payment transaction.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Customer who made this payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate unique transaction ID.
     */
    public static function generateTransactionId(string $prefix = 'TXN'): string
    {
        return strtoupper($prefix . '-' . date('YmdHis') . '-' . Str::random(6));
    }
}

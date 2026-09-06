<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_slug',
        'restaurant_name',
        'product_image',
        'color',
        'size',
        'selected_addons',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantity'        => 'integer',
            'selected_addons' => 'array',
            'unit_price'      => 'decimal:2',
            'subtotal'        => 'decimal:2',
        ];
    }

    /**
     * Parent order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Associated product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Helper to get full image URL for order item.
     */
    public function getImageUrlAttribute(): string
    {
        if (empty($this->product_image)) {
            return 'https://placehold.co/200x200/f1f5f9/475569?text=' . urlencode(substr($this->product_name, 0, 10));
        }

        if (str_starts_with($this->product_image, 'http://') || str_starts_with($this->product_image, 'https://')) {
            return $this->product_image;
        }

        return asset('storage/' . $this->product_image);
    }

    /**
     * Check if item has selected add-ons.
     */
    public function hasAddons(): bool
    {
        return is_array($this->selected_addons) && !empty($this->selected_addons);
    }

    /**
     * Get list of selected add-ons.
     *
     * @return array<int, array{name: string, price: float}>
     */
    public function addonsList(): array
    {
        if (!$this->hasAddons()) {
            return [];
        }

        return array_values($this->selected_addons);
    }

    /**
     * Formatted string of selected add-ons.
     */
    public function formattedAddons(): string
    {
        if (!$this->hasAddons()) {
            return '';
        }

        $names = array_map(function ($addon) {
            $price = isset($addon['price']) && (float) $addon['price'] > 0 
                ? ' (+₹' . number_format((float) $addon['price'], 0) . ')' 
                : '';
            return ($addon['name'] ?? 'Extra') . $price;
        }, $this->addonsList());

        return implode(', ', $names);
    }
}

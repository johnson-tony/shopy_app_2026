<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $table = 'cart_items';

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'color',
        'size',
        'selected_addons',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity'        => 'integer',
            'color'           => 'string',
            'size'            => 'string',
            'selected_addons' => 'array',
            'unit_price'      => 'decimal:2',
        ];
    }

    /**
     * Parent cart relationship.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Associated product relationship.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate line item total price (quantity * unit_price).
     */
    public function lineTotal(): float
    {
        return round((float) $this->unit_price * (int) $this->quantity, 2);
    }

    /**
     * Check if requested quantity is available in product stock.
     */
    public function isAvailable(): bool
    {
        return $this->product && $this->product->status && $this->product->stock >= $this->quantity;
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

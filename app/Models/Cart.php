<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';

    protected $fillable = [
        'user_id',
        'session_id',
        'mode_id',
        'coupon_code',
        'discount_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'discount_amount' => 'decimal:2',
        ];
    }

    /**
     * User relationship (null for guests).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Shopping mode relationship (Shopy, Minutes, Food).
     */
    public function mode(): BelongsTo
    {
        return $this->belongsTo(Mode::class);
    }

    /**
     * Cart line items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Products through cart items.
     */
    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(Product::class, CartItem::class, 'cart_id', 'id', 'id', 'product_id');
    }

    /**
     * Restaurant associated with current food cart items (if any).
     */
    public function currentRestaurant(): ?Restaurant
    {
        $item = $this->items()->whereHas('product', fn ($q) => $q->whereNotNull('restaurant_id'))->first();

        return $item?->product?->restaurant;
    }

    // -------------------------------------------------------------
    // Calculations & Totals
    // -------------------------------------------------------------

    /**
     * Calculate cart subtotal (sum of line totals).
     */
    public function subtotal(): float
    {
        return round($this->items->sum(fn ($item) => (float) $item->unit_price * (int) $item->quantity), 2);
    }

    /**
     * Get free delivery threshold for this cart's shopping mode.
     */
    public function freeDeliveryThreshold(): float
    {
        $modeSlug = $this->mode?->slug ?? 'shopy';

        return match ($modeSlug) {
            'minutes' => 199.00,
            'food'    => 350.00,
            default   => 499.00, // Shopy
        };
    }

    /**
     * Base delivery fee for this cart's shopping mode.
     */
    public function baseDeliveryFee(): float
    {
        $modeSlug = $this->mode?->slug ?? 'shopy';

        return match ($modeSlug) {
            'minutes' => 25.00,
            'food'    => 30.00,
            default   => 50.00, // Shopy
        };
    }

    /**
     * Calculated delivery fee (₹0 if subtotal exceeds threshold or cart is empty).
     */
    public function deliveryFee(): float
    {
        $subtotal = $this->subtotal();

        if ($subtotal <= 0) {
            return 0.00;
        }

        if ($subtotal >= $this->freeDeliveryThreshold()) {
            return 0.00;
        }

        return $this->baseDeliveryFee();
    }

    /**
     * Amount remaining to unlock free delivery.
     */
    public function amountNeededForFreeDelivery(): float
    {
        $subtotal = $this->subtotal();
        $threshold = $this->freeDeliveryThreshold();

        if ($subtotal <= 0 || $subtotal >= $threshold) {
            return 0.00;
        }

        return round($threshold - $subtotal, 2);
    }

    /**
     * Estimated tax (5% GST).
     */
    public function taxAmount(): float
    {
        $subtotal = $this->subtotal();

        if ($subtotal <= 0) {
            return 0.00;
        }

        return round($subtotal * 0.05, 2);
    }

    /**
     * Total discount applied.
     */
    public function discount(): float
    {
        return (float) $this->discount_amount;
    }

    /**
     * Calculate grand total = Subtotal - Discount + Delivery Fee + Tax.
     */
    public function grandTotal(): float
    {
        $subtotal = $this->subtotal();

        if ($subtotal <= 0) {
            return 0.00;
        }

        $total = $subtotal - $this->discount() + $this->deliveryFee() + $this->taxAmount();

        return round(max(0.00, $total), 2);
    }

    /**
     * Count total units in the cart.
     */
    public function totalQuantity(): int
    {
        return (int) $this->items->sum('quantity');
    }

    // -------------------------------------------------------------
    // Item Operations
    // -------------------------------------------------------------

    /**
     * Add product to cart or increment existing quantity.
     *
     * @throws \InvalidArgumentException if product is out of stock
     */
    public function addItem(Product $product, int $quantity = 1, ?string $color = null, ?string $size = null, ?array $selectedAddons = null): CartItem
    {
        if ($product->stock <= 0) {
            throw new \InvalidArgumentException("Sorry, {$product->name} is currently out of stock.");
        }

        // Calculate effective price including add-ons (e.g. Biryani + Salna + Coke)
        $addonsSum = 0.00;
        $normalizedAddons = null;
        if (is_array($selectedAddons) && !empty($selectedAddons)) {
            $normalizedAddons = [];
            foreach ($selectedAddons as $addon) {
                if (is_array($addon) && !empty($addon['name'])) {
                    $price = (float) ($addon['price'] ?? 0.00);
                    $addonsSum += $price;
                    $normalizedAddons[] = [
                        'name'  => (string) $addon['name'],
                        'price' => $price,
                    ];
                }
            }
        }

        $basePrice = $product->is_on_sale ? (float) $product->sale_price : (float) $product->price;
        $effectivePrice = round($basePrice + $addonsSum, 2);

        $addonsJson = $normalizedAddons ? json_encode($normalizedAddons) : null;

        $item = $this->items()
            ->where('product_id', $product->id)
            ->when($color !== null && $color !== '', fn ($q) => $q->where('color', $color), fn ($q) => $q->whereNull('color'))
            ->when($size !== null && $size !== '', fn ($q) => $q->where('size', $size), fn ($q) => $q->whereNull('size'))
            ->when($addonsJson !== null, fn ($q) => $q->where('selected_addons', $addonsJson), fn ($q) => $q->whereNull('selected_addons'))
            ->first();

        if ($item) {
            $newQuantity = $item->quantity + $quantity;
            if ($newQuantity > $product->stock) {
                $newQuantity = $product->stock;
            }
            $item->quantity = $newQuantity;
            $item->unit_price = $effectivePrice; // Update to latest price
            $item->save();
        } else {
            $initialQty = min($quantity, $product->stock);
            $item = $this->items()->create([
                'product_id'      => $product->id,
                'quantity'        => $initialQty,
                'color'           => ($color !== '' ? $color : null),
                'size'            => ($size !== '' ? $size : null),
                'selected_addons' => $normalizedAddons,
                'unit_price'      => $effectivePrice,
            ]);
        }

        // Refresh items relation cache
        $this->unsetRelation('items');

        return $item;
    }

    /**
     * Update quantity of an item in the cart.
     */
    public function updateItem(int $productId, int $quantity, ?int $cartItemId = null): ?CartItem
    {
        $query = $this->items();
        if ($cartItemId) {
            $query->where('id', $cartItemId);
        } else {
            $query->where('product_id', $productId);
        }
        $item = $query->first();

        if (!$item) {
            return null;
        }

        if ($quantity <= 0) {
            $item->delete();
            $this->unsetRelation('items');
            return null;
        }

        $product = $item->product;
        if ($product && $quantity > $product->stock) {
            $quantity = $product->stock;
        }

        $item->quantity = $quantity;
        $item->save();

        $this->unsetRelation('items');

        return $item;
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(int $productId, ?int $cartItemId = null): bool
    {
        $query = $this->items();
        if ($cartItemId) {
            $query->where('id', $cartItemId);
        } else {
            $query->where('product_id', $productId);
        }
        $deleted = $query->delete();
        $this->unsetRelation('items');

        return $deleted > 0;
    }

    /**
     * Empty all items from this cart.
     */
    public function clear(): void
    {
        $this->items()->delete();
        $this->coupon_code = null;
        $this->discount_amount = 0.00;
        $this->save();
        $this->unsetRelation('items');
    }

    // -------------------------------------------------------------
    // Cart Resolution & Customer Merging
    // -------------------------------------------------------------

    /**
     * Get or create active cart for user or guest session scoped to a shopping mode.
     */
    public static function getOrCreate(?User $user, string $sessionId, string|int $modeSlugOrId): self
    {
        // Resolve mode
        $mode = is_numeric($modeSlugOrId)
            ? Mode::find($modeSlugOrId)
            : Mode::where('slug', $modeSlugOrId)->first();

        if (!$mode) {
            $mode = Mode::where('slug', 'shopy')->first() ?? Mode::first();
        }

        if ($user) {
            return self::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'mode_id' => $mode->id,
                ],
                [
                    'session_id'      => $sessionId,
                    'discount_amount' => 0.00,
                ]
            );
        }

        return self::firstOrCreate(
            [
                'session_id' => $sessionId,
                'mode_id'    => $mode->id,
                'user_id'    => null,
            ],
            [
                'discount_amount' => 0.00,
            ]
        );
    }

    /**
     * Seamlessly merge guest session carts into the authenticated user account upon login.
     */
    public static function mergeGuestCart(string $sessionId, int $userId): void
    {
        $guestCarts = self::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->with('items.product')
            ->get();

        foreach ($guestCarts as $guestCart) {
            $userCart = self::firstOrCreate(
                [
                    'user_id' => $userId,
                    'mode_id' => $guestCart->mode_id,
                ],
                [
                    'coupon_code'     => $guestCart->coupon_code,
                    'discount_amount' => $guestCart->discount_amount,
                ]
            );

            foreach ($guestCart->items as $guestItem) {
                if ($guestItem->product) {
                    $userCart->addItem($guestItem->product, $guestItem->quantity);
                }
            }

            // Remove temporary guest cart
            $guestCart->delete();
        }
    }
}

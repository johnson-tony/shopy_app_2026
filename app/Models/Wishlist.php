<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    use HasFactory;

    protected $table = 'wishlists';

    protected $fillable = [
        'user_id',
        'product_id',
    ];

    /**
     * Get the user who owns this wishlist item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the wishlisted product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if a product is wishlisted by a user.
     */
    public static function isWishlisted(int $userId, int $productId): bool
    {
        return self::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Toggle wishlist status for a product and user.
     *
     * @return array{action: string, in_wishlist: bool, count: int}
     */
    public static function toggle(int $userId, int $productId): array
    {
        $existing = self::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            $inWishlist = false;
            $action = 'removed';
        } else {
            self::create([
                'user_id' => $userId,
                'product_id' => $productId,
            ]);
            $inWishlist = true;
            $action = 'added';
        }

        $count = self::where('user_id', $userId)->count();

        return [
            'action' => $action,
            'in_wishlist' => $inWishlist,
            'count' => $count,
        ];
    }
}

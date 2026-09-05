<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductReview extends Model
{
    use HasFactory;

    protected $table = 'product_reviews';

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'title',
        'comment',
        'images',
        'is_verified_buyer',
        'status',
        'helpful_count',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'images' => 'array',
            'is_verified_buyer' => 'boolean',
            'status' => 'boolean',
            'helpful_count' => 'integer',
        ];
    }

    /**
     * Product this review belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Author/customer who wrote the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Order associated with this review (if purchased).
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope to visible/approved reviews.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Scope to verified buyer reviews.
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified_buyer', true);
    }

    /**
     * Get list of full URLs for review photos.
     *
     * @return array<int, string>
     */
    public function imageUrls(): array
    {
        if (!is_array($this->images) || empty($this->images)) {
            return [];
        }

        $urls = [];
        foreach ($this->images as $path) {
            if (empty($path)) {
                continue;
            }
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                $urls[] = $path;
            } elseif (Storage::disk('public')->exists($path)) {
                $urls[] = Storage::disk('public')->url($path);
            } else {
                $urls[] = asset('storage/' . $path);
            }
        }

        return $urls;
    }
}

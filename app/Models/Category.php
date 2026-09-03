<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'mode_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'icon',
        'sort_order',
        'status',
        'is_featured',
        'meta_title',
        'meta_description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'mode_id' => 'integer',
        'parent_id' => 'integer',
        'sort_order' => 'integer',
        'status' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Shopping mode relationship.
     */
    public function mode(): BelongsTo
    {
        return $this->belongsTo(Mode::class);
    }

    /**
     * Parent category relationship.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Direct subcategories relationship.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order', 'asc');
    }

    /**
     * Recursive subcategories relationship.
     */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    /**
     * Scope query to root (top-level) categories only.
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope query to active categories.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Scope query to featured categories.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope query to categories belonging to a specific mode (by mode ID or mode slug).
     */
    public function scopeForMode(Builder $query, int|string $mode): Builder
    {
        if (is_numeric($mode)) {
            return $query->where('mode_id', (int) $mode);
        }
        return $query->whereHas('mode', fn (Builder $q) => $q->where('slug', $mode));
    }

    /**
     * Alias accessor for active status.
     */
    public function getIsActiveAttribute(): bool
    {
        return (bool) $this->status;
    }

    /**
     * Get full image URL (supports Cloudinary secure URLs and fallback storage URLs).
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image)) {
            return null;
        }

        // Cloudinary URL (starts with http:// or https://)
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        // Local storage disk fallback
        if (Storage::disk('public')->exists($this->image)) {
            return Storage::disk('public')->url($this->image);
        }

        return null;
    }

    /**
     * Full breadcrumb path (e.g. "Fashion > Men > Shirts").
     */
    public function getBreadcrumbAttribute(): string
    {
        $ancestors = collect();
        $current = $this;

        while ($current) {
            $ancestors->prepend($current->name);
            $current = $current->parent;
        }

        return $ancestors->implode(' > ');
    }

    /**
     * Get all descendant IDs recursively to prevent circular parent assignments.
     *
     * @return array<int>
     */
    public function getDescendantIds(): array
    {
        $ids = [];
        $children = Category::where('parent_id', $this->id)->get();

        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getDescendantIds());
        }

        return $ids;
    }

    /**
     * Generate a unique slug from category name.
     */
    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}

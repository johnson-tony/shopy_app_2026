<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'module',
        'description',
    ];

    /**
     * The roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions')->withTimestamps();
    }

    /**
     * Get all permissions grouped by their functional module.
     *
     * @return Collection<string, Collection<int, Permission>>
     */
    public static function groupedByModule(): \Illuminate\Support\Collection
    {
        return static::orderBy('module', 'asc')->orderBy('slug', 'asc')->get()->groupBy(function ($perm) {
            return !empty($perm->module) ? ucfirst($perm->module) : 'General';
        });
    }
}

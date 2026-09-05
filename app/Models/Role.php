<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
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
        'description',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * The administrators that belong to the role.
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'admin_roles')->withTimestamps();
    }

    /**
     * The permissions that belong to the role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')->withTimestamps();
    }

    /**
     * Shopping modes accessible to this role.
     */
    public function modes(): BelongsToMany
    {
        return $this->belongsToMany(Mode::class, 'role_modes')->withTimestamps();
    }

    /**
     * Scope query to active roles.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Assign a permission to this role.
     */
    public function givePermissionTo(Permission|string $permission): void
    {
        $permissionModel = is_string($permission) 
            ? Permission::where('slug', $permission)->firstOrFail() 
            : $permission;

        if (!$this->permissions()->where('permissions.id', $permissionModel->id)->exists()) {
            $this->permissions()->attach($permissionModel->id);
        }
    }

    /**
     * Revoke a permission from this role.
     */
    public function revokePermissionTo(Permission|string $permission): void
    {
        $permissionModel = is_string($permission) 
            ? Permission::where('slug', $permission)->first() 
            : $permission;

        if ($permissionModel) {
            $this->permissions()->detach($permissionModel->id);
        }
    }

    /**
     * Check if the role has a given permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions->contains(
            fn (Permission $p) => $p->slug === $permission || $p->name === $permission
        );
    }

    /**
     * Check if the role has access to a specific mode.
     */
    public function hasModeAccess(int|string|Mode $mode): bool
    {
        if ($this->slug === 'super-admin') {
            return true;
        }

        if ($mode instanceof Mode) {
            $modeId = $mode->id;
        } elseif (is_numeric($mode)) {
            $modeId = (int) $mode;
        } else {
            return $this->modes->contains('slug', $mode);
        }

        return $this->modes->contains('id', $modeId);
    }

    /**
     * Grant mode access to this role.
     */
    public function giveModeAccess(Mode|int|string $mode): void
    {
        if ($mode instanceof Mode) {
            $modeId = $mode->id;
        } elseif (is_numeric($mode)) {
            $modeId = (int) $mode;
        } else {
            $modeId = Mode::where('slug', $mode)->value('id');
        }

        if ($modeId && !$this->modes()->where('modes.id', $modeId)->exists()) {
            $this->modes()->attach($modeId);
        }
    }

    /**
     * Revoke mode access from this role.
     */
    public function revokeModeAccess(Mode|int|string $mode): void
    {
        if ($mode instanceof Mode) {
            $modeId = $mode->id;
        } elseif (is_numeric($mode)) {
            $modeId = (int) $mode;
        } else {
            $modeId = Mode::where('slug', $mode)->value('id');
        }

        if ($modeId) {
            $this->modes()->detach($modeId);
        }
    }

    /**
     * Sync mode access list.
     */
    public function syncModes(array $modeIds): void
    {
        $this->modes()->sync($modeIds);
    }
}

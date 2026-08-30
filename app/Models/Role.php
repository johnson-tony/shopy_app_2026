<?php

namespace App\Models;

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
}

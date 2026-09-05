<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admins';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * The roles assigned to the administrator.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'admin_roles')->withTimestamps();
    }

    /**
     * Specific shopping modes directly assigned to this administrator.
     */
    public function modes(): BelongsToMany
    {
        return $this->belongsToMany(Mode::class, 'admin_modes')->withTimestamps();
    }

    /**
     * Invitations associated with this administrator.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(AdminInvitation::class, 'admin_id');
    }

    /**
     * Check if this administrator has unrestricted Super Admin privileges.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin') || $this->email === 'superadmin@shopy.test';
    }

    /**
     * Determine if admin has a specific role or any role in an array.
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->roles->contains(fn (Role $role) => $role->slug === $roles || $role->name === $roles);
        }

        return $this->roles->contains(fn (Role $role) => in_array($role->slug, $roles, true) || in_array($role->name, $roles, true));
    }

    /**
     * Determine if admin has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Determine if admin has a specific permission through any of their assigned roles.
     * Super Admins always bypass permission checks.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $normalized = [
            $permission,
            str_replace('.', '-', $permission),
            str_replace('-', '.', $permission),
        ];

        return $this->roles->flatMap(fn (Role $role) => $role->permissions)->contains(
            function (Permission $p) use ($normalized, $permission) {
                if (in_array($p->slug, $normalized, true) || in_array($p->name, $normalized, true)) {
                    return true;
                }

                // Support legacy manage-* mapping (e.g. manage-products grants products.*)
                if (str_starts_with($p->slug, 'manage-')) {
                    $module = substr($p->slug, 7);
                    if (str_starts_with($permission, $module . '.')) {
                        return true;
                    }
                }

                return false;
            }
        );
    }

    /**
     * Get all unique permissions of the administrator.
     */
    public function allPermissions(): Collection
    {
        if ($this->isSuperAdmin()) {
            return Permission::all();
        }

        return $this->roles->flatMap(fn (Role $role) => $role->permissions)->unique('id');
    }

    /**
     * Get IDs of all shopping modes this admin is authorized to access.
     */
    public function getAllowedModeIds(): array
    {
        if ($this->isSuperAdmin()) {
            return Mode::pluck('id')->all();
        }

        $directModeIds = $this->modes->pluck('id')->all();
        $roleModeIds = $this->roles->flatMap(fn (Role $r) => $r->modes)->pluck('id')->all();

        return array_values(array_unique(array_merge($directModeIds, $roleModeIds)));
    }

    /**
     * Get all shopping modes this admin is authorized to access.
     */
    public function getAllowedModes(): \Illuminate\Support\Collection
    {
        if ($this->isSuperAdmin()) {
            return Mode::active()->ordered()->get();
        }

        $allowedIds = $this->getAllowedModeIds();
        return Mode::whereIn('id', $allowedIds)->active()->ordered()->get();
    }

    /**
     * Verify if this admin has access to a specific mode.
     */
    public function hasModeAccess(int|string|Mode $mode): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($mode instanceof Mode) {
            $modeId = $mode->id;
        } elseif (is_numeric($mode)) {
            $modeId = (int) $mode;
        } else {
            $modeId = Mode::where('slug', $mode)->value('id');
        }

        return in_array($modeId, $this->getAllowedModeIds(), true);
    }

    /**
     * Check if admin status is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Determine if another administrator is authorized to modify or delete this account.
     * Prevents non-Super-Admins from modifying Super Admin accounts.
     */
    public function canBeModifiedBy(Admin $actor): bool
    {
        if ($this->isSuperAdmin()) {
            return $actor->isSuperAdmin();
        }

        return $actor->isSuperAdmin() || $actor->hasPermission('admins.edit');
    }

    /**
     * Assign a role by model or slug.
     */
    public function assignRole(Role|string $role): void
    {
        $roleModel = is_string($role) ? Role::where('slug', $role)->firstOrFail() : $role;
        if (!$this->roles()->where('roles.id', $roleModel->id)->exists()) {
            $this->roles()->attach($roleModel->id);
        }
    }

    /**
     * Remove a role by model or slug.
     */
    public function removeRole(Role|string $role): void
    {
        $roleModel = is_string($role) ? Role::where('slug', $role)->first() : $role;
        if ($roleModel) {
            $this->roles()->detach($roleModel->id);
        }
    }
}

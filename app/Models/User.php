<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_PENDING = 'pending';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_BLOCKED,
        self::STATUS_PENDING,
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
        'email_verified_at',
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The roles assigned to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')->withTimestamps();
    }

    /**
     * Determine if user has a specific role or any role in an array.
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->roles->contains(fn (Role $role) => $role->slug === $roles || $role->name === $roles);
        }

        return $this->roles->contains(fn (Role $role) => in_array($role->slug, $roles, true) || in_array($role->name, $roles, true));
    }

    /**
     * Determine if user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Determine if user has a specific permission through any of their assigned roles.
     */
    public function hasPermission(string $permission): bool
    {
        // Super admin has all permissions
        if ($this->hasRole('super-admin')) {
            return true;
        }

        return $this->roles->flatMap(fn (Role $role) => $role->permissions)->contains(
            fn (Permission $p) => $p->slug === $permission || $p->name === $permission
        );
    }

    /**
     * Get all unique permissions of the user.
     */
    public function allPermissions(): Collection
    {
        return $this->roles->flatMap(fn (Role $role) => $role->permissions)->unique('id');
    }

    /**
     * Check if user is an admin or super admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(['admin', 'super-admin']);
    }

    /**
     * Check if user is a customer.
     */
    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }

    /**
     * Check if user status is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
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

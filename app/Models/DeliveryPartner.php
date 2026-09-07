<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class DeliveryPartner extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'delivery_partners';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_SUSPENDED,
    ];

    public const LOCATION_SOURCE_STATIC = 'static';
    public const LOCATION_SOURCE_GPS = 'gps';

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
        'avatar',
        'vehicle_type',
        'status',
        'is_available',
        'latitude',
        'longitude',
        'location_source',
        'last_location_at',
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

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_available' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'last_location_at' => 'datetime',
        ];
    }

    /**
     * The shopping modes this partner is authorized to fulfil.
     */
    public function modes(): BelongsToMany
    {
        return $this->belongsToMany(Mode::class, 'delivery_partner_modes')->withTimestamps();
    }

    /**
     * Orders currently or previously assigned to this partner.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'delivery_partner_id');
    }

    /**
     * Live location history broadcast by this partner.
     */
    public function locations(): HasMany
    {
        return $this->hasMany(PartnerLocation::class);
    }

    /**
     * Check whether the partner account is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check whether the partner is available (online) for new assignments.
     */
    public function isAvailable(): bool
    {
        return $this->isActive() && $this->is_available;
    }
}

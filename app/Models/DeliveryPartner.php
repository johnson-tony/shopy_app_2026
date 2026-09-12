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

    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

    public const STATUSES = [
        self::STATUS_PENDING_APPROVAL,
        self::STATUS_ACTIVE,
        self::STATUS_REJECTED,
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
        'vehicle_number',
        'license_number',
        'license_image',
        'id_proof_type',
        'id_proof_number',
        'id_proof_image',
        'bank_account_number',
        'bank_ifsc',
        'upi_id',
        'rejection_reason',
        'approved_at',
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
            'approved_at' => 'datetime',
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
     * Orders currently or previously assigned to this partner for delivery.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'delivery_partner_id');
    }

    /**
     * Return pickup orders assigned to this partner.
     */
    public function returnOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'return_partner_id');
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

    public function isPendingApproval(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Check whether the partner is available (online) for new assignments.
     */
    public function isAvailable(): bool
    {
        return $this->isActive() && $this->is_available;
    }

    /**
     * Update current GPS location and log history point.
     */
    public function updateLocation(float $latitude, float $longitude, ?float $accuracy = null): void
    {
        $this->update([
            'latitude'         => $latitude,
            'longitude'        => $longitude,
            'location_source'  => self::LOCATION_SOURCE_GPS,
            'last_location_at' => now(),
        ]);

        $this->locations()->create([
            'latitude'    => $latitude,
            'longitude'   => $longitude,
            'accuracy'    => $accuracy,
            'recorded_at' => now(),
        ]);
    }

    /**
     * Human-friendly status badge styling.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => [
                'label'  => 'Active / Verified',
                'bg'     => 'bg-emerald-500/10',
                'text'   => 'text-emerald-400',
                'border' => 'border-emerald-500/20',
                'icon'   => 'fa-solid fa-circle-check',
            ],
            self::STATUS_PENDING_APPROVAL => [
                'label'  => 'KYC Pending Review',
                'bg'     => 'bg-amber-500/10',
                'text'   => 'text-amber-400',
                'border' => 'border-amber-500/20',
                'icon'   => 'fa-solid fa-clock',
            ],
            self::STATUS_REJECTED => [
                'label'  => 'KYC Declined',
                'bg'     => 'bg-rose-500/10',
                'text'   => 'text-rose-400',
                'border' => 'border-rose-500/20',
                'icon'   => 'fa-solid fa-circle-xmark',
            ],
            self::STATUS_SUSPENDED => [
                'label'  => 'Suspended',
                'bg'     => 'bg-rose-500/20',
                'text'   => 'text-rose-300',
                'border' => 'border-rose-500/30',
                'icon'   => 'fa-solid fa-ban',
            ],
            default => [
                'label'  => ucfirst($this->status),
                'bg'     => 'bg-slate-500/10',
                'text'   => 'text-slate-400',
                'border' => 'border-slate-500/20',
                'icon'   => 'fa-solid fa-circle-pause',
            ],
        };
    }
}

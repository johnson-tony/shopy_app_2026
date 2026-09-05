<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AdminInvitation extends Model
{
    use HasFactory;

    protected $table = 'admin_invitations';

    protected $fillable = [
        'admin_id',
        'name',
        'email',
        'token',
        'role_id',
        'mode_ids',
        'expires_at',
        'accepted_at',
        'created_by',
    ];

    protected $casts = [
        'mode_ids' => 'array',
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    /**
     * Associated admin account.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Assigned role for the invited admin.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Super Admin who issued the invitation.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Check if the invitation has expired.
     */
    public function isExpired(): bool
    {
        return Carbon::now()->greaterThan($this->expires_at);
    }

    /**
     * Check if the invitation has been accepted.
     */
    public function isAccepted(): bool
    {
        return !is_null($this->accepted_at);
    }

    /**
     * Check if the invitation is currently valid and pending.
     */
    public function isValid(): bool
    {
        return !$this->isAccepted() && !$this->isExpired();
    }

    /**
     * Generate a cryptographically secure, URL-safe random token.
     */
    public static function generateUniqueToken(): string
    {
        do {
            $token = Str::random(48);
        } while (static::where('token', $token)->exists());

        return $token;
    }
}

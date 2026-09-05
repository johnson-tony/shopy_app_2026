<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UserEmailVerification extends Model
{
    use HasFactory;

    protected $table = 'user_email_verifications';

    protected $fillable = [
        'user_id',
        'email',
        'otp',
        'token',
        'expires_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Get the associated user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the verification has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the verification is already used.
     */
    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    /**
     * Check if given OTP matches and is still valid.
     */
    public function isValid(string $inputOtp): bool
    {
        if ($this->isExpired() || $this->isVerified()) {
            return false;
        }

        return hash_equals($this->otp, trim($inputOtp));
    }

    /**
     * Mark this verification record as verified.
     */
    public function markAsVerified(): bool
    {
        return $this->update([
            'verified_at' => now(),
        ]);
    }

    /**
     * Generate a new verification record for a user.
     */
    public static function generateForUser(User $user, int $expiryMinutes = 15): self
    {
        // 6-digit numeric OTP code
        $otp = sprintf('%06d', random_int(100000, 999999));
        // 64-character URL-safe token for cross-device one-click link
        $token = Str::random(64);

        return self::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => $otp,
            'token' => $token,
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UserPasswordResetOtp extends Model
{
    use HasFactory;

    protected $table = 'user_password_reset_otps';

    protected $fillable = [
        'user_id',
        'email',
        'otp',
        'token',
        'expires_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
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
     * Check if the reset code has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the reset code has already been used.
     */
    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }

    /**
     * Check if given OTP is valid.
     */
    public function isValid(string $inputOtp): bool
    {
        if ($this->isExpired() || $this->isUsed()) {
            return false;
        }

        return hash_equals($this->otp, trim($inputOtp));
    }

    /**
     * Mark this reset record as used.
     */
    public function markAsUsed(): bool
    {
        return $this->update([
            'used_at' => now(),
        ]);
    }

    /**
     * Generate a new password reset OTP and token for a user.
     */
    public static function generateForUser(User $user, int $expiryMinutes = 15): self
    {
        // Expire any prior unused reset OTPs for this user
        self::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['expires_at' => now()]);

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

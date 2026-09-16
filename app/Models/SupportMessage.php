<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportMessage extends Model
{
    use HasFactory;

    public const SENDER_USER = 'user';
    public const SENDER_ADMIN = 'admin';
    public const SENDER_SYSTEM = 'system';

    protected $fillable = [
        'support_ticket_id',
        'sender_type',
        'user_id',
        'admin_id',
        'message',
        'attachment',
        'is_internal',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function getSenderNameAttribute(): string
    {
        if ($this->sender_type === self::SENDER_ADMIN) {
            return $this->admin?->name ?? 'Support Team';
        }

        if ($this->sender_type === self::SENDER_SYSTEM) {
            return 'System Notice';
        }

        return $this->user?->name ?? $this->ticket?->name ?? 'Customer';
    }

    public function isFromAdmin(): bool
    {
        return $this->sender_type === self::SENDER_ADMIN;
    }

    public function isFromUser(): bool
    {
        return $this->sender_type === self::SENDER_USER;
    }
}

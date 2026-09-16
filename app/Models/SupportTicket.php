<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_IN_PROGRESS,
        self::STATUS_RESOLVED,
        self::STATUS_CLOSED,
    ];

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    public const CATEGORIES = [
        'delivery_delay' => 'Delivery Delay / Rider Not Moving',
        'partner_issue'  => 'Delivery Partner Related Issue',
        'order_inquiry'  => 'Order Status & Tracking Inquiry',
        'damaged_items'  => 'Damaged / Missing Items in Package',
        'return_refund'  => 'Return Pickup & Refund Status',
        'payment_issue'  => 'Payment / COD Collection Dispute',
        'general'        => 'General Inquiries & Assistance',
    ];

    protected $fillable = [
        'ticket_number',
        'user_id',
        'order_id',
        'name',
        'email',
        'phone',
        'subject',
        'category',
        'priority',
        'status',
        'assigned_admin_id',
        'partner_contacted',
        'partner_contact_notes',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'partner_contacted' => 'boolean',
            'resolved_at'       => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SupportTicket $ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = 'TIC-' . strtoupper(Str::random(8));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_admin_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'support_ticket_id');
    }

    public function customerMessages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'support_ticket_id')->where('is_internal', false);
    }

    public function getDeliveryPartnerAttribute(): ?DeliveryPartner
    {
        return $this->order?->deliveryPartner;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_IN_PROGRESS], true);
    }

    public function isResolved(): bool
    {
        return in_array($this->status, [self::STATUS_RESOLVED, self::STATUS_CLOSED], true);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            self::STATUS_OPEN => [
                'label'  => 'Open',
                'bg'     => 'bg-amber-500/10',
                'text'   => 'text-amber-400',
                'border' => 'border-amber-500/20',
                'icon'   => 'fa-solid fa-clock',
            ],
            self::STATUS_IN_PROGRESS => [
                'label'  => 'In Progress',
                'bg'     => 'bg-indigo-500/10',
                'text'   => 'text-indigo-400',
                'border' => 'border-indigo-500/20',
                'icon'   => 'fa-solid fa-spinner',
            ],
            self::STATUS_RESOLVED => [
                'label'  => 'Resolved',
                'bg'     => 'bg-emerald-500/10',
                'text'   => 'text-emerald-400',
                'border' => 'border-emerald-500/20',
                'icon'   => 'fa-solid fa-circle-check',
            ],
            self::STATUS_CLOSED => [
                'label'  => 'Closed',
                'bg'     => 'bg-slate-500/10',
                'text'   => 'text-slate-400',
                'border' => 'border-slate-500/20',
                'icon'   => 'fa-solid fa-lock',
            ],
            default => [
                'label'  => ucfirst($this->status),
                'bg'     => 'bg-slate-500/10',
                'text'   => 'text-slate-400',
                'border' => 'border-slate-500/20',
                'icon'   => 'fa-solid fa-circle-info',
            ],
        };
    }

    public function getPriorityBadgeAttribute(): array
    {
        return match ($this->priority) {
            self::PRIORITY_URGENT => [
                'label'  => 'Urgent',
                'bg'     => 'bg-rose-500/10',
                'text'   => 'text-rose-400',
                'border' => 'border-rose-500/20',
            ],
            self::PRIORITY_HIGH => [
                'label'  => 'High',
                'bg'     => 'bg-orange-500/10',
                'text'   => 'text-orange-400',
                'border' => 'border-orange-500/20',
            ],
            self::PRIORITY_LOW => [
                'label'  => 'Low',
                'bg'     => 'bg-slate-500/10',
                'text'   => 'text-slate-400',
                'border' => 'border-slate-500/20',
            ],
            default => [
                'label'  => 'Normal',
                'bg'     => 'bg-blue-500/10',
                'text'   => 'text-blue-400',
                'border' => 'border-blue-500/20',
            ],
        };
    }
}

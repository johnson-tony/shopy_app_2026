<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    /**
     * Display a listing of customer support tickets.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->input('status', 'all');
        $categoryFilter = $request->input('category');
        $priorityFilter = $request->input('priority');
        $search = $request->string('search')->trim()->toString();

        $query = SupportTicket::with(['order.deliveryPartner', 'user', 'messages'])
            ->withCount('messages');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$search}%"));
            });
        }

        if ($statusFilter && $statusFilter !== 'all' && in_array($statusFilter, SupportTicket::STATUSES, true)) {
            $query->where('status', $statusFilter);
        }

        if ($categoryFilter && array_key_exists($categoryFilter, SupportTicket::CATEGORIES)) {
            $query->where('category', $categoryFilter);
        }

        if ($priorityFilter && in_array($priorityFilter, ['low', 'normal', 'high', 'urgent'], true)) {
            $query->where('priority', $priorityFilter);
        }

        $tickets = $query->orderByDesc('id')->paginate(15)->withQueryString();

        $stats = [
            'total'             => SupportTicket::count(),
            'open'              => SupportTicket::where('status', SupportTicket::STATUS_OPEN)->count(),
            'inProgress'        => SupportTicket::where('status', SupportTicket::STATUS_IN_PROGRESS)->count(),
            'resolved'          => SupportTicket::where('status', SupportTicket::STATUS_RESOLVED)->count(),
            'partnerIssues'     => SupportTicket::whereIn('category', ['partner_issue', 'delivery_delay'])->whereIn('status', [SupportTicket::STATUS_OPEN, SupportTicket::STATUS_IN_PROGRESS])->count(),
        ];

        return view('admin.support.index', compact('tickets', 'stats', 'statusFilter', 'categoryFilter', 'priorityFilter', 'search'));
    }

    /**
     * Display ticket workspace with customer chat, order details, and delivery partner call card.
     */
    public function show(SupportTicket $ticket): View
    {
        $ticket->load([
            'order.deliveryPartner',
            'order.items',
            'user',
            'assignedAdmin',
            'messages.user',
            'messages.admin',
        ]);

        $allAdmins = Admin::where('status', Admin::STATUS_ACTIVE)->get();

        return view('admin.support.show', compact('ticket', 'allAdmins'));
    }

    /**
     * Post a reply to customer or an internal staff note.
     */
    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'message'     => ['required', 'string', 'min:2', 'max:5000'],
            'is_internal' => ['nullable', 'boolean'],
            'new_status'  => ['nullable', 'string', 'in:' . implode(',', SupportTicket::STATUSES)],
            'attachment'  => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('support/attachments', 'public');
        }

        $isInternal = (bool) ($validated['is_internal'] ?? false);

        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_type'       => SupportMessage::SENDER_ADMIN,
            'admin_id'          => $admin?->id,
            'message'           => $validated['message'],
            'attachment'        => $attachmentPath,
            'is_internal'       => $isInternal,
        ]);

        // Update status if selected, otherwise set to in_progress if still open
        if (!empty($validated['new_status'])) {
            $updateData = ['status' => $validated['new_status']];
            if ($validated['new_status'] === SupportTicket::STATUS_RESOLVED && !$ticket->resolved_at) {
                $updateData['resolved_at'] = now();
            }
            $ticket->update($updateData);
        } elseif (!$isInternal && $ticket->status === SupportTicket::STATUS_OPEN) {
            $ticket->update(['status' => SupportTicket::STATUS_IN_PROGRESS]);
        }

        $msgType = $isInternal ? 'Internal note added' : 'Reply sent to customer';
        return back()->with('success', $msgType . ' successfully.');
    }

    /**
     * Log a phone call made by the admin to the assigned delivery partner.
     */
    public function logPartnerCall(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'call_notes' => ['required', 'string', 'max:1000'],
        ]);

        $partnerName = $ticket->order?->deliveryPartner?->name ?? 'Delivery Partner';
        $partnerPhone = $ticket->order?->deliveryPartner?->phone ?? 'N/A';

        $timestamp = now()->format('d M, h:i A');
        $logEntry = "[{$timestamp}] Staff ({$admin?->name}) contacted driver {$partnerName} ({$partnerPhone}): {$validated['call_notes']}";

        $currentNotes = $ticket->partner_contact_notes ? $ticket->partner_contact_notes . "\n" . $logEntry : $logEntry;

        $ticket->update([
            'partner_contacted'     => true,
            'partner_contact_notes' => $currentNotes,
            'status'                => $ticket->status === SupportTicket::STATUS_OPEN ? SupportTicket::STATUS_IN_PROGRESS : $ticket->status,
        ]);

        // Create an internal note entry in the ticket chat timeline
        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_type'       => SupportMessage::SENDER_ADMIN,
            'admin_id'          => $admin?->id,
            'message'           => "📞 **Delivery Partner Call Log**\n" . $validated['call_notes'],
            'is_internal'       => true,
        ]);

        return back()->with('success', "Call with delivery partner {$partnerName} logged successfully.");
    }

    /**
     * Update ticket status or assign admin.
     */
    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status'            => ['required', 'string', 'in:' . implode(',', SupportTicket::STATUSES)],
            'assigned_admin_id' => ['nullable', 'exists:admins,id'],
        ]);

        $updateData = [
            'status' => $validated['status'],
        ];

        if (array_key_exists('assigned_admin_id', $validated)) {
            $updateData['assigned_admin_id'] = $validated['assigned_admin_id'];
        }

        if ($validated['status'] === SupportTicket::STATUS_RESOLVED && !$ticket->resolved_at) {
            $updateData['resolved_at'] = now();
        }

        $ticket->update($updateData);

        return back()->with('success', "Ticket status updated to " . ucfirst($validated['status']));
    }
}

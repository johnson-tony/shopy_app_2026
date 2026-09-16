<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupportController extends Controller
{
    /**
     * Display the Support Hub with call center helpline and customer tickets.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $myTickets = $user
            ? SupportTicket::with(['order', 'messages'])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(6)
            : collect();

        $openCount = $user
            ? SupportTicket::where('user_id', $user->id)->whereIn('status', [SupportTicket::STATUS_OPEN, SupportTicket::STATUS_IN_PROGRESS])->count()
            : 0;

        return view('user.pages.support.index', compact('myTickets', 'openCount'));
    }

    /**
     * Show form to open a support ticket (optionally linked to a specific order).
     */
    public function create(Request $request): View
    {
        $user = Auth::user();
        $selectedOrder = null;

        if ($request->filled('order_number')) {
            $selectedOrder = Order::where('order_number', $request->input('order_number'))
                ->when($user, fn ($q) => $q->where('user_id', $user->id))
                ->first();
        } elseif ($request->filled('order_id')) {
            $selectedOrder = Order::where('id', $request->input('order_id'))
                ->when($user, fn ($q) => $q->where('user_id', $user->id))
                ->first();
        }

        $userOrders = $user
            ? Order::where('user_id', $user->id)->latest()->take(10)->get()
            : collect();

        return view('user.pages.support.create', compact('selectedOrder', 'userOrders'));
    }

    /**
     * Store a new support ticket and its initial message.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'email'      => ['required', 'string', 'email', 'max:150'],
            'phone'      => ['nullable', 'string', 'max:25'],
            'subject'    => ['required', 'string', 'max:200'],
            'category'   => ['required', 'string', 'in:' . implode(',', array_keys(SupportTicket::CATEGORIES))],
            'priority'   => ['nullable', 'string', 'in:low,normal,high,urgent'],
            'order_id'   => ['nullable', 'exists:orders,id'],
            'message'    => ['required', 'string', 'min:5', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('support/attachments', 'public');
        }

        $ticket = SupportTicket::create([
            'user_id'   => $user?->id,
            'order_id'  => $validated['order_id'] ?? null,
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'subject'   => $validated['subject'],
            'category'  => $validated['category'],
            'priority'  => $validated['priority'] ?? SupportTicket::PRIORITY_NORMAL,
            'status'    => SupportTicket::STATUS_OPEN,
        ]);

        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_type'       => SupportMessage::SENDER_USER,
            'user_id'           => $user?->id,
            'message'           => $validated['message'],
            'attachment'        => $attachmentPath,
        ]);

        return redirect()->route('support.show', $ticket->ticket_number)
            ->with('success', "Support request #{$ticket->ticket_number} submitted! An agent will respond shortly.");
    }

    /**
     * Display the ticket conversation chat view.
     */
    public function show(string $ticketNumber): View
    {
        $user = Auth::user();

        $ticket = SupportTicket::with([
            'order.deliveryPartner',
            'order.items',
            'messages.user',
            'messages.admin',
        ])->where('ticket_number', $ticketNumber)->firstOrFail();

        // Customer privacy check
        if ($ticket->user_id && (!$user || $user->id !== $ticket->user_id)) {
            abort(403, 'Unauthorized to view this support ticket.');
        }

        return view('user.pages.support.show', compact('ticket'));
    }

    /**
     * Customer sends a reply in the chat.
     */
    public function reply(Request $request, string $ticketNumber): RedirectResponse
    {
        $user = Auth::user();

        $ticket = SupportTicket::where('ticket_number', $ticketNumber)->firstOrFail();

        if ($ticket->user_id && (!$user || $user->id !== $ticket->user_id)) {
            abort(403, 'Unauthorized to reply to this ticket.');
        }

        $validated = $request->validate([
            'message'    => ['required', 'string', 'min:2', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('support/attachments', 'public');
        }

        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_type'       => SupportMessage::SENDER_USER,
            'user_id'           => $user?->id,
            'message'           => $validated['message'],
            'attachment'        => $attachmentPath,
        ]);

        // Re-open ticket if it was resolved/closed
        if ($ticket->status !== SupportTicket::STATUS_OPEN) {
            $ticket->update(['status' => SupportTicket::STATUS_OPEN]);
        }

        return back()->with('success', 'Your reply has been sent to our support team.');
    }
}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation #{{ $order->order_number }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f172a; color: #f8fafc; padding: 40px 16px; margin: 0;">
    <div style="max-width: 620px; margin: 0 auto; background-color: #1e293b; border-radius: 20px; border: 1px solid #334155; padding: 32px 24px; box-shadow: 0 12px 30px rgba(0,0,0,0.5);">
        
        <!-- Header / Brand -->
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="display: inline-block; padding: 10px 22px; border-radius: 12px; background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.3);">
                <span style="font-size: 22px; font-weight: 900; color: #818cf8; letter-spacing: -0.5px;">SHOPY 2026</span>
            </div>
            <div style="margin-top: 14px;">
                <span style="display: inline-block; background-color: #065f46; color: #34d399; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 4px 12px; border-radius: 20px;">
                    ✓ Order Confirmed
                </span>
            </div>
            <h1 style="font-size: 22px; font-weight: 800; color: #ffffff; margin: 12px 0 6px 0;">Thank You for Your Order!</h1>
            <p style="font-size: 13px; color: #94a3b8; margin: 0;">Hi {{ $order->shipping_name ?? $order->user?->name }}, we've received your order and are getting it ready.</p>
        </div>

        <!-- Order Summary Card -->
        <div style="background-color: #0f172a; border-radius: 14px; padding: 20px; border: 1px solid #334155; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 12px; margin-bottom: 14px;">
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600; display: block;">Order Number</span>
                    <strong style="font-size: 15px; color: #ffffff; font-family: monospace;">{{ $order->order_number }}</strong>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600; display: block;">Order Date</span>
                    <strong style="font-size: 13px; color: #cbd5e1;">{{ $order->created_at->format('M d, Y • h:i A') }}</strong>
                </div>
            </div>

            <!-- 1-Click Track Button -->
            <div style="text-align: center; padding: 12px 0;">
                <a href="{{ $trackUrl }}" target="_blank" rel="noopener noreferrer" style="display: inline-block; width: 85%; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: #ffffff; font-size: 14px; font-weight: 700; text-decoration: none; padding: 14px 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4); text-align: center;">
                    🚚 Track Your Order in Real-Time &rarr;
                </a>
                <p style="font-size: 11px; color: #64748b; margin: 8px 0 0 0;">Click anytime to see live order milestones, delivery partner info, and estimated arrival.</p>
            </div>
        </div>

        <!-- Items Ordered -->
        <div style="background-color: #0f172a; border-radius: 14px; padding: 20px; border: 1px solid #334155; margin-bottom: 20px;">
            <h3 style="font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin: 0 0 14px 0; font-weight: 700;">
                Items in This Order ({{ $order->items->count() }})
            </h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tbody>
                    @foreach($order->items as $item)
                        <tr style="border-bottom: 1px solid #1e293b;">
                            <td style="padding: 10px 0; vertical-align: middle;">
                                <strong style="font-size: 13px; color: #ffffff; display: block;">{{ $item->product_name }}</strong>
                                <span style="font-size: 11px; color: #64748b;">
                                    Qty: {{ $item->quantity }}
                                    @if($item->color) • Color: {{ ucfirst($item->color) }} @endif
                                    @if($item->size) • Size: {{ strtoupper($item->size) }} @endif
                                </span>
                            </td>
                            <td style="padding: 10px 0; text-align: right; vertical-align: middle; font-size: 13px; font-weight: 700; color: #ffffff;">
                                ₹{{ number_format($item->subtotal, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Financial Summary -->
            <table style="width: 100%; margin-top: 14px; font-size: 12px; color: #94a3b8; border-collapse: collapse;">
                <tr>
                    <td style="padding: 4px 0;">Subtotal</td>
                    <td style="padding: 4px 0; text-align: right; color: #cbd5e1;">₹{{ number_format($order->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;">Delivery Fee</td>
                    <td style="padding: 4px 0; text-align: right; color: #cbd5e1;">
                        {{ $order->delivery_fee > 0 ? '₹' . number_format($order->delivery_fee, 2) : 'FREE' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;">Taxes (GST)</td>
                    <td style="padding: 4px 0; text-align: right; color: #cbd5e1;">₹{{ number_format($order->tax_amount, 2) }}</td>
                </tr>
                @if($order->discount_amount > 0)
                    <tr>
                        <td style="padding: 4px 0; color: #34d399;">Discount ({{ $order->coupon_code }})</td>
                        <td style="padding: 4px 0; text-align: right; color: #34d399;">-₹{{ number_format($order->discount_amount, 2) }}</td>
                    </tr>
                @endif
                <tr style="border-top: 1px solid #334155;">
                    <td style="padding: 10px 0; font-size: 14px; font-weight: 800; color: #ffffff;">Grand Total</td>
                    <td style="padding: 10px 0; font-size: 16px; font-weight: 900; color: #818cf8; text-align: right;">
                        ₹{{ number_format($order->grand_total, 2) }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Delivery & Payment Grid -->
        <div style="display: table; width: 100%; margin-bottom: 20px;">
            <div style="display: table-row;">
                <!-- Delivery Details -->
                <div style="display: table-cell; width: 50%; vertical-align: top; padding-right: 8px;">
                    <div style="background-color: #0f172a; border-radius: 14px; padding: 16px; border: 1px solid #334155; height: 100%;">
                        <span style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; display: block; margin-bottom: 8px;">
                            📍 Delivery Address
                        </span>
                        <strong style="font-size: 13px; color: #ffffff; display: block;">{{ $order->shipping_name ?? $order->user?->name }}</strong>
                        <p style="font-size: 12px; color: #94a3b8; margin: 4px 0; line-height: 1.4;">
                            {{ $order->formatted_shipping_address ?? 'Address on file' }}
                        </p>
                        @if($order->shipping_phone)
                            <p style="font-size: 11px; color: #64748b; margin: 6px 0 0 0;">
                                Phone: {{ $order->shipping_phone }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Payment Details -->
                <div style="display: table-cell; width: 50%; vertical-align: top; padding-left: 8px;">
                    <div style="background-color: #0f172a; border-radius: 14px; padding: 16px; border: 1px solid #334155; height: 100%;">
                        <span style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; display: block; margin-bottom: 8px;">
                            💳 Payment Info
                        </span>
                        <div style="margin-bottom: 6px;">
                            <span style="font-size: 11px; color: #64748b;">Method:</span>
                            <strong style="font-size: 12px; color: #ffffff; display: block;">
                                @if($order->payment_method === 'pay_on_delivery')
                                    Cash on Delivery (Doorstep)
                                @elseif($order->payment_method === 'mock_upi')
                                    UPI (PhonePe / GPay / Paytm)
                                @elseif($order->payment_method === 'mock_card')
                                    Credit / Debit Card
                                @else
                                    {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}
                                @endif
                            </strong>
                        </div>
                        <div>
                            <span style="font-size: 11px; color: #64748b;">Status:</span>
                            <span style="display: inline-block; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px; {{ $order->payment_status === 'paid' ? 'background: #065f46; color: #34d399;' : 'background: #78350f; color: #fde68a;' }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="border-top: 1px solid #334155; padding-top: 18px; font-size: 11px; color: #64748b; text-align: center; line-height: 1.5;">
            <p style="margin: 0 0 6px 0;">Need help with this order? Visit our <a href="{{ url('/') }}" style="color: #818cf8; text-decoration: none;">Help Center</a> or track live in your account.</p>
            <p style="margin: 0;">&copy; {{ date('Y') }} Shopy Platform. All rights reserved.</p>
        </div>

    </div>
</body>
</html>

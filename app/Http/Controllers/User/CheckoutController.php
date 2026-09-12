<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmationMail;
use App\Models\AdminSetting;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Display the Checkout page with address selection, items summary, and payment options.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('login')->with('info', 'Please sign in to proceed with checkout.');
        }

        $activeMode = session('active_shopping_mode', 'shopy');
        $cart = Cart::getOrCreate($user, $request->session()->getId(), $activeMode);
        $cart->load(['items.product.category', 'mode']);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', 'Your cart is empty. Please add items before checking out.');
        }

        // Fetch user's saved addresses
        $addresses = $user->addresses()->latest()->get();
        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();

        // Calculations
        $subtotal = $cart->subtotal();
        $deliveryFee = $cart->deliveryFee();
        $taxAmount = $cart->taxAmount();
        $discountAmount = $cart->discount();
        $grandTotal = $cart->grandTotal();
        $freeDeliveryThreshold = $cart->freeDeliveryThreshold();

        $isCodEnabled = AdminSetting::isCodEnabled();
        $isUpiEnabled = AdminSetting::isUpiEnabled();
        $isCardEnabled = AdminSetting::isCardEnabled();
        $upiId = AdminSetting::upiId();
        $upiMerchantName = AdminSetting::upiMerchantName();
        $upiQrImageUrl = AdminSetting::upiQrImageUrl();

        return view('user.pages.checkout', compact(
            'user',
            'cart',
            'addresses',
            'defaultAddress',
            'subtotal',
            'deliveryFee',
            'taxAmount',
            'discountAmount',
            'grandTotal',
            'freeDeliveryThreshold',
            'isCodEnabled',
            'isUpiEnabled',
            'isCardEnabled',
            'upiId',
            'upiMerchantName',
            'upiQrImageUrl'
        ));
    }

    /**
     * Process checkout form submission and create confirmed Order.
     */
    public function placeOrder(Request $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $activeMode = session('active_shopping_mode', 'shopy');
        $cart = Cart::getOrCreate($user, $request->session()->getId(), $activeMode);
        $cart->load(['items.product', 'mode']);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', 'Your cart is empty. Please add items before placing an order.');
        }

        // Check stock availability
        foreach ($cart->items as $item) {
            if (!$item->product || !$item->product->status) {
                return back()->with('error', "Sorry, item '{$item->product?->name}' is no longer available.")->withInput();
            }
            if ($item->product->stock < $item->quantity) {
                return back()->with('error', "Sorry, '{$item->product->name}' only has {$item->product->stock} units left in stock.")->withInput();
            }
        }

        // Validation rules
        $rules = [
            'address_source' => 'required|in:existing,new',
            'payment_method' => 'required|string|in:pay_on_delivery,mock_upi,mock_card,mock_netbanking',
            'notes'          => 'nullable|string|max:500',
        ];

        $messages = [
            'address_id.required'     => 'Please select a saved delivery address from your profile.',
            'address_id.exists'       => 'The selected delivery address is no longer available.',
            'full_name.required'      => 'Recipient full name is required.',
            'phone.required'          => 'Contact phone number is required.',
            'phone.regex'             => 'Please enter a valid phone number (at least 10 digits).',
            'address_line1.required'  => 'Street address / Flat / House number is required.',
            'city.required'           => 'City is required.',
            'state.required'          => 'State is required.',
            'postal_code.required'    => 'PIN code / Postal code is required.',
            'postal_code.regex'       => 'Please enter a valid PIN or postal code.',
            'address_type.required'   => 'Please select an address type (Home, Work, or Other).',
            'payment_method.required' => 'Please select a payment method.',
        ];

        if ($request->input('address_source') === 'existing') {
            $rules['address_id'] = 'required|exists:user_addresses,id';
        } else {
            $rules['full_name']     = ['required', 'string', 'max:100'];
            $rules['phone']         = ['required', 'string', 'min:10', 'max:20', 'regex:/^[0-9+\s\-\(\)]{10,20}$/'];
            $rules['address_line1'] = ['required', 'string', 'max:255'];
            $rules['address_line2'] = ['nullable', 'string', 'max:255'];
            $rules['landmark']      = ['nullable', 'string', 'max:255'];
            $rules['city']          = ['required', 'string', 'max:100'];
            $rules['state']         = ['required', 'string', 'max:100'];
            $rules['postal_code']   = ['required', 'string', 'min:5', 'max:10', 'regex:/^[0-9A-Za-z\s\-]{5,10}$/'];
            $rules['address_type']  = ['required', 'in:home,work,other'];
        }

        $validated = $request->validate($rules, $messages);

        // Resolve delivery address id
        if ($validated['address_source'] === 'existing') {
            $address = $user->addresses()->where('id', $validated['address_id'])->firstOrFail();
            $addressId = $address->id;
        } else {
            // Always save new address to user profile address book so they have a persistent address
            $address = $user->addresses()->create([
                'full_name'     => $validated['full_name'],
                'phone'         => $validated['phone'],
                'address_line1' => $validated['address_line1'],
                'address_line2' => $validated['address_line2'] ?? null,
                'landmark'      => $validated['landmark'] ?? null,
                'city'          => $validated['city'],
                'state'         => $validated['state'],
                'postal_code'   => $validated['postal_code'],
                'address_type'  => $validated['address_type'],
                'country'       => 'India',
                'is_default'    => $user->addresses()->count() === 0,
            ]);

            $addressId = $address->id;
        }

        $paymentMethod = $validated['payment_method'];
        $paymentStatus = ($paymentMethod === Order::PAYMENT_METHOD_COD)
            ? Order::PAYMENT_STATUS_PENDING
            : Order::PAYMENT_STATUS_PAID;

        // Perform transactional order creation
        $order = DB::transaction(function () use (
            $user,
            $cart,
            $address,
            $paymentMethod,
            $paymentStatus,
            $validated,
            $request
        ) {
            $order = Order::create([
                'order_number'          => Order::generateOrderNumber(),
                'user_id'               => $user->id,
                'mode_id'               => $cart->mode_id,
                'address_id'            => $address->id,
                'shipping_name'         => $address->full_name,
                'shipping_phone'        => $address->phone,
                'shipping_address_line1'=> $address->address_line1,
                'shipping_address_line2'=> $address->address_line2,
                'shipping_landmark'     => $address->landmark,
                'shipping_city'         => $address->city,
                'shipping_state'        => $address->state,
                'shipping_postal_code'  => $address->postal_code,
                'shipping_country'      => $address->country ?? 'India',
                'shipping_address_type' => $address->address_type ?? 'home',
                'status'                => Order::STATUS_CONFIRMED,
                'payment_method'        => $paymentMethod,
                'payment_status'        => $paymentStatus,
                'subtotal'              => $cart->subtotal(),
                'delivery_fee'          => $cart->deliveryFee(),
                'tax_amount'            => $cart->taxAmount(),
                'discount_amount'       => $cart->discount(),
                'coupon_code'           => $cart->coupon_code,
                'grand_total'           => $cart->grandTotal(),
                'notes'                 => $validated['notes'] ?? null,
            ]);

            // Create items and decrement product stock
            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id'        => $order->id,
                    'product_id'      => $cartItem->product_id,
                    'product_name'    => $cartItem->product->name,
                    'product_slug'    => $cartItem->product->slug,
                    'restaurant_name' => $cartItem->product->restaurant?->name,
                    'product_image'   => $cartItem->product->image,
                    'color'           => $cartItem->color,
                    'size'            => $cartItem->size,
                    'selected_addons' => $cartItem->selected_addons,
                    'quantity'        => $cartItem->quantity,
                    'unit_price'      => $cartItem->unit_price,
                    'subtotal'        => round($cartItem->unit_price * $cartItem->quantity, 2),
                ]);

                // Decrement inventory stock safely
                $cartItem->product->decrement('stock', $cartItem->quantity);
            }

            // Record coupon usage if coupon was applied
            if (!empty($cart->coupon_code)) {
                $coupon = Coupon::where('code', $cart->coupon_code)->first();
                if ($coupon) {
                    $coupon->recordUsage($user->id, (float) $cart->discount_amount, $order->id);
                }
            }

            // Record payment transaction ledger entry
            Payment::create([
                'order_id'        => $order->id,
                'user_id'         => $user->id,
                'payment_method'  => $paymentMethod,
                'payment_gateway' => match ($paymentMethod) {
                    Order::PAYMENT_METHOD_COD => Payment::GATEWAY_MANUAL,
                    Order::PAYMENT_METHOD_MOCK_UPI => Payment::GATEWAY_DIRECT_UPI,
                    Order::PAYMENT_METHOD_MOCK_CARD => Payment::GATEWAY_SIMULATED_CARD,
                    default => Payment::GATEWAY_MANUAL,
                },
                'transaction_id'  => Payment::generateTransactionId('TXN'),
                'amount'          => $order->grand_total,
                'currency'        => 'INR',
                'status'          => ($paymentStatus === Order::PAYMENT_STATUS_PAID) ? Payment::STATUS_COMPLETED : Payment::STATUS_PENDING,
                'notes'           => $validated['notes'] ?? null,
                'payload'         => [
                    'ip'         => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'placed_at'  => now()->toIso8601String(),
                ],
            ]);

            // Clear the active cart
            $cart->items()->delete();
            $cart->update([
                'coupon_code'     => null,
                'discount_amount' => 0.00,
                'notes'           => null,
            ]);

            return $order;
        });

        // Dispatch Order Confirmation & Live Tracking Email (fail-safe)
        try {
            if (!empty($user->email)) {
                Mail::to($user->email)->send(new OrderConfirmationMail($order));
            }
        } catch (\Throwable $e) {
            Log::error('Order Confirmation Email dispatch error: ' . $e->getMessage(), [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'email'        => $user->email,
            ]);
        }

        return redirect()->route('orders.success', $order->order_number)
            ->with('success', 'Your order has been placed successfully!');
    }
}

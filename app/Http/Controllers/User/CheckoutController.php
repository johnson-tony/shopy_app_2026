<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        return view('user.pages.checkout', compact(
            'cart',
            'addresses',
            'defaultAddress',
            'subtotal',
            'deliveryFee',
            'taxAmount',
            'discountAmount',
            'grandTotal',
            'freeDeliveryThreshold'
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

        if ($request->input('address_source') === 'existing') {
            $rules['address_id'] = 'required|exists:user_addresses,id';
        } else {
            $rules['full_name']     = 'required|string|max:100';
            $rules['phone']         = 'required|string|max:20';
            $rules['address_line1'] = 'required|string|max:255';
            $rules['address_line2'] = 'nullable|string|max:255';
            $rules['landmark']      = 'nullable|string|max:255';
            $rules['city']          = 'required|string|max:100';
            $rules['state']         = 'required|string|max:100';
            $rules['postal_code']   = 'required|string|max:12';
            $rules['address_type']  = 'required|in:home,work,other';
        }

        $validated = $request->validate($rules);

        // Resolve shipping address data
        if ($validated['address_source'] === 'existing') {
            $address = $user->addresses()->where('id', $validated['address_id'])->firstOrFail();
            $shippingName = $address->full_name;
            $shippingPhone = $address->phone;
            $shippingLine1 = $address->address_line1;
            $shippingLine2 = $address->address_line2;
            $shippingLandmark = $address->landmark;
            $shippingCity = $address->city;
            $shippingState = $address->state;
            $shippingPostal = $address->postal_code;
            $shippingType = $address->address_type ?? 'home';
            $shippingCountry = $address->country ?? 'India';
        } else {
            $shippingName = $validated['full_name'];
            $shippingPhone = $validated['phone'];
            $shippingLine1 = $validated['address_line1'];
            $shippingLine2 = $validated['address_line2'] ?? null;
            $shippingLandmark = $validated['landmark'] ?? null;
            $shippingCity = $validated['city'];
            $shippingState = $validated['state'];
            $shippingPostal = $validated['postal_code'];
            $shippingType = $validated['address_type'];
            $shippingCountry = 'India';

            // Optional save to address book
            if ($request->boolean('save_address')) {
                $user->addresses()->create([
                    'full_name'     => $shippingName,
                    'phone'         => $shippingPhone,
                    'address_line1' => $shippingLine1,
                    'address_line2' => $shippingLine2,
                    'landmark'      => $shippingLandmark,
                    'city'          => $shippingCity,
                    'state'         => $shippingState,
                    'postal_code'   => $shippingPostal,
                    'address_type'  => $shippingType,
                    'country'       => $shippingCountry,
                    'is_default'    => $user->addresses()->count() === 0,
                ]);
            }
        }

        $paymentMethod = $validated['payment_method'];
        $paymentStatus = ($paymentMethod === Order::PAYMENT_METHOD_COD)
            ? Order::PAYMENT_STATUS_PENDING
            : Order::PAYMENT_STATUS_PAID;

        // Perform transactional order creation
        $order = DB::transaction(function () use (
            $user,
            $cart,
            $shippingName,
            $shippingPhone,
            $shippingLine1,
            $shippingLine2,
            $shippingLandmark,
            $shippingCity,
            $shippingState,
            $shippingPostal,
            $shippingCountry,
            $shippingType,
            $paymentMethod,
            $paymentStatus,
            $validated
        ) {
            $order = Order::create([
                'order_number'          => Order::generateOrderNumber(),
                'user_id'               => $user->id,
                'mode_id'               => $cart->mode_id,
                'shipping_name'         => $shippingName,
                'shipping_phone'        => $shippingPhone,
                'shipping_address_line1'=> $shippingLine1,
                'shipping_address_line2'=> $shippingLine2,
                'shipping_landmark'     => $shippingLandmark,
                'shipping_city'         => $shippingCity,
                'shipping_state'        => $shippingState,
                'shipping_postal_code'  => $shippingPostal,
                'shipping_country'      => $shippingCountry,
                'shipping_address_type' => $shippingType,
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
                    'order_id'      => $order->id,
                    'product_id'    => $cartItem->product_id,
                    'product_name'  => $cartItem->product->name,
                    'product_slug'  => $cartItem->product->slug,
                    'product_image' => $cartItem->product->image,
                    'color'         => $cartItem->color,
                    'size'          => $cartItem->size,
                    'quantity'      => $cartItem->quantity,
                    'unit_price'    => $cartItem->unit_price,
                    'subtotal'      => round($cartItem->unit_price * $cartItem->quantity, 2),
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

            // Clear the active cart
            $cart->items()->delete();
            $cart->update([
                'coupon_code'     => null,
                'discount_amount' => 0.00,
                'notes'           => null,
            ]);

            return $order;
        });

        return redirect()->route('orders.success', $order->order_number)
            ->with('success', 'Your order has been placed successfully!');
    }
}

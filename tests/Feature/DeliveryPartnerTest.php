<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminSetting;
use App\Models\DeliveryPartner;
use App\Models\Mode;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DeliveryPartnerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new DatabaseSeeder())->run();
    }

    protected function getPartner(): DeliveryPartner
    {
        return DeliveryPartner::where('email', 'rider@shopy.test')->firstOrFail();
    }

    protected function getSuperAdmin(): Admin
    {
        return Admin::where('email', 'superadmin@shopy.test')->firstOrFail();
    }

    // ------------------------------------------------------------------
    // Schema & Model
    // ------------------------------------------------------------------

    public function test_delivery_partners_tables_exist(): void
    {
        $partnerColumns = Schema::getColumnListing('delivery_partners');
        $this->assertContains('name', $partnerColumns);
        $this->assertContains('email', $partnerColumns);
        $this->assertContains('is_available', $partnerColumns);
        $this->assertContains('latitude', $partnerColumns);
        $this->assertContains('longitude', $partnerColumns);
        $this->assertContains('location_source', $partnerColumns);

        $this->assertTrue(Schema::hasTable('partner_locations'));
        $this->assertTrue(Schema::hasTable('partner_password_reset_tokens'));
        $this->assertTrue(Schema::hasTable('delivery_partner_modes'));

        $orderColumns = Schema::getColumnListing('orders');
        $this->assertContains('delivery_partner_id', $orderColumns);
        $this->assertContains('assigned_at', $orderColumns);
        $this->assertContains('ready_for_delivery_at', $orderColumns);
        $this->assertContains('picked_up_at', $orderColumns);
        $this->assertContains('out_for_delivery_at', $orderColumns);
    }

    public function test_partner_casts_and_relations(): void
    {
        $partner = $this->getPartner();

        $this->assertTrue($partner->is_available);
        $this->assertTrue($partner->isActive());
        $this->assertTrue($partner->isAvailable());

        $modeSlugs = $partner->modes->pluck('slug');
        $this->assertContains('minutes', $modeSlugs);
        $this->assertContains('food', $modeSlugs);
        $this->assertContains('shopy', $modeSlugs);
    }

    // ------------------------------------------------------------------
    // Partner Auth
    // ------------------------------------------------------------------

    public function test_partner_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/partner/login');

        $response->assertStatus(200);
        $response->assertSee('Delivery Partner Portal');
        $response->assertSee('Go On-Duty');
    }

    public function test_guest_cannot_access_partner_dashboard(): void
    {
        $response = $this->get('/partner/dashboard');

        $response->assertRedirect('/partner/login');
    }

    public function test_customer_session_cannot_access_partner_dashboard(): void
    {
        $customer = User::firstOrFail();
        $response = $this->actingAs($customer, 'web')->get('/partner/dashboard');

        $response->assertRedirect('/partner/login');
    }

    public function test_partner_can_authenticate_via_partner_login(): void
    {
        $response = $this->post('/partner/login', [
            'email' => 'rider@shopy.test',
            'password' => 'password',
        ]);

        $this->assertAuthenticated('partner');
        $response->assertRedirect('/partner/dashboard');
    }

    public function test_login_with_invalid_credentials_fails(): void
    {
        $response = $this->post('/partner/login', [
            'email' => 'rider@shopy.test',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest('partner');
        $response->assertSessionHasErrors('email');
    }

    public function test_inactive_partner_cannot_login(): void
    {
        $partner = $this->getPartner();
        $partner->update(['status' => DeliveryPartner::STATUS_INACTIVE]);

        $response = $this->post('/partner/login', [
            'email' => 'rider@shopy.test',
            'password' => 'password',
        ]);

        $this->assertGuest('partner');
        $response->assertRedirect('/partner/login');
        $response->assertSessionHas('error');
    }

    public function test_authenticated_partner_visiting_login_redirects_to_dashboard(): void
    {
        $partner = $this->getPartner();

        $response = $this->actingAs($partner, 'partner')->get('/partner/login');

        $response->assertRedirect('/partner/dashboard');
    }

    public function test_authenticated_partner_can_access_dashboard(): void
    {
        $partner = $this->getPartner();

        $response = $this->actingAs($partner, 'partner')->get('/partner/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_partner_can_logout(): void
    {
        $partner = $this->getPartner();

        $this->actingAs($partner, 'partner')->post('/partner/logout');

        $this->assertGuest('partner');
    }

    // ------------------------------------------------------------------
    // Delivery Settings (Admin Toggle)
    // ------------------------------------------------------------------

    public function test_admin_settings_table_has_delivery_columns(): void
    {
        $columns = Schema::getColumnListing('admin_settings');
        $this->assertContains('is_delivery_enabled', $columns);
        $this->assertContains('delivery_enabled_shopy', $columns);
        $this->assertContains('delivery_enabled_minutes', $columns);
        $this->assertContains('delivery_enabled_food', $columns);
    }

    public function test_delivery_defaults_match_design(): void
    {
        $this->assertTrue(AdminSetting::isDeliveryEnabled());
        $this->assertFalse(AdminSetting::isDeliveryEnabledForMode('shopy'));
        $this->assertTrue(AdminSetting::isDeliveryEnabledForMode('minutes'));
        $this->assertFalse(AdminSetting::isDeliveryEnabledForMode('food'));
    }

    public function test_admin_settings_page_shows_delivery_card(): void
    {
        $admin = $this->getSuperAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Enable Delivery System', false);
        $response->assertSee('Shopy (E-commerce)', false);
        $response->assertSee('Minutes (Quick Commerce)', false);
        $response->assertSee('Food Delivery', false);
    }

    public function test_admin_can_toggle_delivery_settings(): void
    {
        $admin = $this->getSuperAdmin();

        $response = $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), [
            'site_name'                 => 'Shopy',
            'is_delivery_enabled'       => '1',
            'delivery_enabled_shopy'    => '1',
            'delivery_enabled_minutes'  => '1',
            'delivery_enabled_food'     => '1',
        ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertTrue(AdminSetting::isDeliveryEnabled());
        $this->assertTrue(AdminSetting::isDeliveryEnabledForMode('shopy'));
        $this->assertTrue(AdminSetting::isDeliveryEnabledForMode('minutes'));
        $this->assertTrue(AdminSetting::isDeliveryEnabledForMode('food'));
    }

    public function test_admin_can_disable_global_delivery(): void
    {
        $admin = $this->getSuperAdmin();

        $response = $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), [
            'site_name'                 => 'Shopy',
            'is_delivery_enabled'       => '0',
            'delivery_enabled_shopy'    => '1',
            'delivery_enabled_minutes'  => '1',
            'delivery_enabled_food'     => '0',
        ]);

        $response->assertRedirect(route('admin.settings.index'));

        $this->assertFalse(AdminSetting::isDeliveryEnabled());
        // Global switch off overrides per-mode flags.
        $this->assertFalse(AdminSetting::isDeliveryEnabledForMode('minutes'));
    }

    // ------------------------------------------------------------------
    // Order Delivery State Machine
    // ------------------------------------------------------------------

    protected function makeOrder(string $status = Order::STATUS_CONFIRMED): Order
    {
        $mode = Mode::where('slug', 'minutes')->firstOrFail();
        $customer = User::firstOrFail();
        $product = Product::where('mode_id', $mode->id)->first() ?? Product::create([
            'mode_id'     => $mode->id,
            'category_id' => $mode->categories()->first()?->id,
            'name'        => 'Test Product',
            'slug'        => 'test-product-' . uniqid(),
            'sku'         => 'TP-' . uniqid(),
            'price'       => 100.00,
            'stock'       => 10,
            'status'      => true,
        ]);

        $address = UserAddress::create([
            'user_id'       => $customer->id,
            'full_name'     => $customer->name,
            'address_line1' => 'Test Address',
            'city'          => 'Bengaluru',
            'state'         => 'Karnataka',
            'postal_code'   => '560001',
            'address_type'  => 'home',
            'is_default'    => true,
        ]);

        return Order::create([
            'order_number'          => Order::generateOrderNumber(),
            'user_id'               => $customer->id,
            'mode_id'               => $mode->id,
            'address_id'            => $address->id,
            'status'                => $status,
            'payment_method'        => 'pay_on_delivery',
            'payment_status'        => 'pending',
            'subtotal'              => 100.00,
            'delivery_fee'          => 0.00,
            'tax_amount'            => 5.00,
            'discount_amount'       => 0.00,
            'grand_total'           => 105.00,
        ]);
    }

    public function test_full_delivery_state_chain_is_valid(): void
    {
        $order = $this->makeOrder(Order::STATUS_CONFIRMED);

        $this->assertTrue($order->canTransitionTo(Order::STATUS_PROCESSING));
        $this->assertTrue($order->canTransitionTo(Order::STATUS_READY_FOR_DELIVERY));
        $this->assertTrue($order->canTransitionTo(Order::STATUS_CANCELLED));
        $this->assertFalse($order->canTransitionTo(Order::STATUS_DELIVERED));

        $order->status = Order::STATUS_READY_FOR_DELIVERY;
        $this->assertTrue($order->canTransitionTo(Order::STATUS_DELIVERY_ASSIGNED));

        $order->status = Order::STATUS_DELIVERY_ASSIGNED;
        $this->assertTrue($order->canTransitionTo(Order::STATUS_PICKED_UP));

        $order->status = Order::STATUS_PICKED_UP;
        $this->assertTrue($order->canTransitionTo(Order::STATUS_OUT_FOR_DELIVERY));

        $order->status = Order::STATUS_OUT_FOR_DELIVERY;
        $this->assertTrue($order->canTransitionTo(Order::STATUS_DELIVERED));
    }

    public function test_shopy_milestone_chain_is_valid(): void
    {
        $order = $this->makeOrder(Order::STATUS_PROCESSING);

        $this->assertTrue($order->canTransitionTo(Order::STATUS_SHIPPED));
        $order->status = Order::STATUS_SHIPPED;
        $this->assertTrue($order->canTransitionTo(Order::STATUS_OUT_FOR_DELIVERY));

        $order->status = Order::STATUS_OUT_FOR_DELIVERY;
        $this->assertTrue($order->canTransitionTo(Order::STATUS_DELIVERED));
    }

    public function test_delivered_and_cancelled_are_terminal(): void
    {
        $delivered = $this->makeOrder(Order::STATUS_DELIVERED);
        $this->assertFalse($delivered->canTransitionTo(Order::STATUS_SHIPPED));

        $cancelled = $this->makeOrder(Order::STATUS_CANCELLED);
        $this->assertFalse($cancelled->canTransitionTo(Order::STATUS_CONFIRMED));
    }

    public function test_admin_cannot_skip_delivery_transitions(): void
    {
        $admin = $this->getSuperAdmin();
        $order = $this->makeOrder(Order::STATUS_CONFIRMED);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.orders.update', $order), [
            'status' => Order::STATUS_DELIVERED,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(Order::STATUS_CONFIRMED, $order->fresh()->status);
    }

    public function test_admin_can_mark_order_ready_for_delivery(): void
    {
        $admin = $this->getSuperAdmin();
        $order = $this->makeOrder(Order::STATUS_PROCESSING);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.orders.update', $order), [
            'status' => Order::STATUS_READY_FOR_DELIVERY,
        ]);

        $response->assertSessionHas('success');
        $fresh = $order->fresh();
        $this->assertEquals(Order::STATUS_READY_FOR_DELIVERY, $fresh->status);
        $this->assertNotNull($fresh->ready_for_delivery_at);
    }
}
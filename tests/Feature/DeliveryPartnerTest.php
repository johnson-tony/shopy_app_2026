<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminSetting;
use App\Models\DeliveryPartner;
use App\Models\Mode;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAddress;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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

    // ------------------------------------------------------------------
    // Partner KYC Onboarding, Admin Review & Order Fulfilment
    // ------------------------------------------------------------------

    public function test_partner_can_register_with_kyc_documents_and_vehicle(): void
    {
        Storage::fake('public');

        $modes = Mode::whereIn('slug', ['shopy', 'minutes'])->pluck('id')->toArray();

        $response = $this->post('/partner/register', [
            'name'                  => 'John Rider',
            'email'                 => 'john.rider@test.com',
            'phone'                 => '9876543210',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
            'vehicle_type'          => 'motorcycle',
            'vehicle_number'        => 'KA-01-AB-1234',
            'modes'                 => $modes,
            'license_number'        => 'DL-KA-2026-999999',
            'license_image'         => UploadedFile::fake()->image('dl.jpg'),
            'id_proof_type'         => 'aadhaar',
            'id_proof_number'       => '1234-5678-9012',
            'id_proof_image'        => UploadedFile::fake()->image('id.jpg'),
            'bank_account_number'   => '123456789012',
            'bank_ifsc'             => 'HDFC0001234',
            'upi_id'                => 'john@upi',
        ]);

        $response->assertRedirect('/partner/login');
        $response->assertSessionHas('success');

        $partner = DeliveryPartner::where('email', 'john.rider@test.com')->first();
        $this->assertNotNull($partner);
        $this->assertEquals(DeliveryPartner::STATUS_PENDING_APPROVAL, $partner->status);
        $this->assertEquals('DL-KA-2026-999999', $partner->license_number);
        $this->assertNotNull($partner->license_image);
        $this->assertNotNull($partner->id_proof_image);
        $this->assertCount(2, $partner->modes);
    }

    public function test_admin_can_approve_kyc_and_activate_partner(): void
    {
        $admin = $this->getSuperAdmin();
        $partner = DeliveryPartner::create([
            'name'         => 'Pending Rider',
            'email'        => 'pending@rider.test',
            'phone'        => '9888877777',
            'password'     => bcrypt('Secret123'),
            'status'       => DeliveryPartner::STATUS_PENDING_APPROVAL,
            'vehicle_type' => 'scooter',
        ]);

        $shopyMode = Mode::where('slug', 'shopy')->firstOrFail();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.delivery_partners.approve', $partner), [
            'modes' => [$shopyMode->id],
        ]);

        $response->assertSessionHas('success');
        $fresh = $partner->fresh();
        $this->assertEquals(DeliveryPartner::STATUS_ACTIVE, $fresh->status);
        $this->assertNotNull($fresh->approved_at);
        $this->assertTrue($fresh->modes->contains($shopyMode->id));
    }

    public function test_admin_can_reject_kyc_with_reason(): void
    {
        $admin = $this->getSuperAdmin();
        $partner = DeliveryPartner::create([
            'name'         => 'Faulty Rider',
            'email'        => 'faulty@rider.test',
            'phone'        => '9777766666',
            'password'     => bcrypt('Secret123'),
            'status'       => DeliveryPartner::STATUS_PENDING_APPROVAL,
            'vehicle_type' => 'bicycle',
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.delivery_partners.reject', $partner), [
            'rejection_reason' => 'Driving license document is blurry and unreadable.',
        ]);

        $response->assertSessionHas('success');
        $fresh = $partner->fresh();
        $this->assertEquals(DeliveryPartner::STATUS_REJECTED, $fresh->status);
        $this->assertEquals('Driving license document is blurry and unreadable.', $fresh->rejection_reason);
    }

    public function test_admin_can_assign_active_partner_to_order(): void
    {
        $admin = $this->getSuperAdmin();
        $partner = $this->getPartner();
        $order = $this->makeOrder(Order::STATUS_CONFIRMED);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.orders.assign_partner', $order), [
            'delivery_partner_id' => $partner->id,
        ]);

        $response->assertSessionHas('success');
        $fresh = $order->fresh();
        $this->assertEquals($partner->id, $fresh->delivery_partner_id);
        $this->assertEquals(Order::STATUS_DELIVERY_ASSIGNED, $fresh->status);
        $this->assertNotNull($fresh->assigned_at);
    }

    public function test_partner_can_fulfill_order_with_proof_of_delivery(): void
    {
        Storage::fake('public');

        $partner = $this->getPartner();
        $order = $this->makeOrder(Order::STATUS_OUT_FOR_DELIVERY);
        $order->delivery_partner_id = $partner->id;
        $order->save();

        $response = $this->actingAs($partner, 'partner')->post(route('partner.orders.deliver', $order), [
            'delivery_proof_image' => UploadedFile::fake()->image('doorstep.jpg'),
            'delivery_notes'       => 'Left package with customer at doorstep.',
            'cod_collected'        => 1,
        ]);

        $response->assertSessionHas('success');
        $fresh = $order->fresh();
        $this->assertEquals(Order::STATUS_DELIVERED, $fresh->status);
        $this->assertEquals(Order::PAYMENT_STATUS_PAID, $fresh->payment_status);
        $this->assertNotNull($fresh->delivery_proof_image);
        $this->assertEquals('Left package with customer at doorstep.', $fresh->delivery_notes);
        $this->assertNotNull($fresh->delivered_at);
    }

    public function test_partner_can_pickup_return_with_photo_proof_and_automatic_restock(): void
    {
        Storage::fake('public');

        $partner = $this->getPartner();
        $order = $this->makeOrder(Order::STATUS_RETURN_APPROVED);
        $order->return_partner_id = $partner->id;
        $order->save();

        $product = Product::firstOrFail();
        $initialStock = $product->stock;

        $order->items()->create([
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'product_slug' => $product->slug,
            'unit_price'   => 100.00,
            'price'        => 100.00,
            'quantity'     => 2,
            'subtotal'     => 200.00,
        ]);

        $response = $this->actingAs($partner, 'partner')->post(route('partner.orders.pickup_return', $order), [
            'return_pickup_image' => UploadedFile::fake()->image('return_box.jpg'),
            'return_pickup_notes' => 'Product inspected with all original tags intact.',
        ]);

        $response->assertSessionHas('success');
        $fresh = $order->fresh();
        $this->assertEquals(Order::STATUS_RETURNED, $fresh->status);
        $this->assertEquals('completed', $fresh->return_status);
        $this->assertNotNull($fresh->return_pickup_image);
        $this->assertNotNull($fresh->return_picked_up_at);

        // Product stock automatically restocked
        $this->assertEquals($initialStock + 2, $product->fresh()->stock);
    }

    public function test_customer_can_query_live_tracking_location(): void
    {
        $partner = $this->getPartner();
        $partner->update([
            'latitude'         => 12.935242,
            'longitude'        => 77.624461,
            'last_location_at' => now(),
        ]);

        $order = $this->makeOrder(Order::STATUS_OUT_FOR_DELIVERY);
        $order->delivery_partner_id = $partner->id;
        $order->save();

        $customer = $order->user;

        $response = $this->actingAs($customer, 'web')->get(route('orders.live_location', $order->order_number));

        $response->assertStatus(200);
        $response->assertJson([
            'status'    => Order::STATUS_OUT_FOR_DELIVERY,
            'is_active' => true,
            'rider'     => [
                'name'      => $partner->name,
                'latitude'  => 12.935242,
                'longitude' => 77.624461,
            ],
        ]);
    }

    // ------------------------------------------------------------------
    // Admin Delivery Partner & KYC Management
    // ------------------------------------------------------------------

    public function test_admin_can_view_delivery_partners_list_and_filter(): void
    {
        $admin = $this->getSuperAdmin();
        $partner = $this->getPartner();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.delivery_partners.index'));

        $response->assertStatus(200);
        $response->assertSee($partner->name);
        $response->assertSee('Pending KYC Review');
        $response->assertSee('Add Delivery Partner');

        // Test filter by status
        $filterResponse = $this->actingAs($admin, 'admin')->get(route('admin.delivery_partners.index', ['status' => 'active']));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee($partner->name);
    }

    public function test_admin_can_view_create_delivery_partner_form(): void
    {
        $admin = $this->getSuperAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.delivery_partners.create'));

        $response->assertStatus(200);
        $response->assertSee('Add Delivery Partner');
        $response->assertSee('Initial Password');
        $response->assertSee('Vehicle Type');
        $response->assertSee('Authorized Shopping Channels');
    }

    public function test_admin_can_create_delivery_partner_with_documents_and_modes(): void
    {
        Storage::fake('public');
        $admin = $this->getSuperAdmin();
        $mode = Mode::firstOrFail();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.delivery_partners.store'), [
            'name'                => 'Vikram Logistics',
            'email'               => 'vikram@delivery.test',
            'phone'               => '+91 9988776655',
            'password'            => 'partnerSecret123',
            'vehicle_type'        => 'motorcycle',
            'vehicle_number'      => 'DL-01-AB-9999',
            'modes'               => [$mode->id],
            'license_number'      => 'DL-99887766554433',
            'license_image'       => UploadedFile::fake()->image('license.jpg'),
            'id_proof_type'       => 'aadhaar',
            'id_proof_number'     => '9988-7766-5544',
            'id_proof_image'      => UploadedFile::fake()->image('aadhaar.jpg'),
            'bank_account_number' => '987654321012',
            'bank_ifsc'           => 'HDFC0001234',
            'upi_id'              => 'vikram@upi',
            'status'              => 'active',
        ]);

        $response->assertRedirect(route('admin.delivery_partners.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('delivery_partners', [
            'email'          => 'vikram@delivery.test',
            'name'           => 'Vikram Logistics',
            'vehicle_type'   => 'motorcycle',
            'vehicle_number' => 'DL-01-AB-9999',
            'status'         => 'active',
            'upi_id'         => 'vikram@upi',
        ]);

        $created = DeliveryPartner::where('email', 'vikram@delivery.test')->firstOrFail();
        $this->assertTrue($created->modes->contains($mode->id));
        $this->assertNotNull($created->license_image);
        $this->assertNotNull($created->id_proof_image);
        Storage::disk('public')->assertExists($created->license_image);
        Storage::disk('public')->assertExists($created->id_proof_image);
    }

    public function test_admin_can_view_delivery_partner_profile_and_kyc(): void
    {
        $admin = $this->getSuperAdmin();
        $partner = $this->getPartner();
        $partner->update([
            'latitude'  => 12.9716,
            'longitude' => 77.5946,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.delivery_partners.show', $partner));

        $response->assertStatus(200);
        $response->assertSee($partner->name);
        $response->assertSee('KYC Onboarding Documents');
        $response->assertSee('GPS Telemetry');
        $response->assertSee('partnerMap');
    }

    public function test_admin_can_view_edit_delivery_partner_form(): void
    {
        $admin = $this->getSuperAdmin();
        $partner = $this->getPartner();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.delivery_partners.edit', $partner));

        $response->assertStatus(200);
        $response->assertSee('Edit Delivery Partner');
        $response->assertSee($partner->email);
        $response->assertSee('Save Partner Changes');
    }

    public function test_admin_can_update_delivery_partner(): void
    {
        $admin = $this->getSuperAdmin();
        $partner = $this->getPartner();
        $mode = Mode::firstOrFail();

        $response = $this->actingAs($admin, 'admin')->put(route('admin.delivery_partners.update', $partner), [
            'name'                => 'Rider Updated Name',
            'email'               => $partner->email,
            'phone'               => '+91 9123456780',
            'vehicle_type'        => 'ev',
            'vehicle_number'      => 'KA-05-EV-1234',
            'modes'               => [$mode->id],
            'license_number'      => 'DL-UPDATED-123',
            'id_proof_type'       => 'pan',
            'id_proof_number'     => 'ABCDE1234F',
            'bank_account_number' => '123456789012',
            'bank_ifsc'           => 'SBIN0001234',
            'upi_id'              => 'updated@upi',
            'status'              => 'active',
        ]);

        $response->assertRedirect(route('admin.delivery_partners.show', $partner));
        $response->assertSessionHas('success');

        $fresh = $partner->fresh();
        $this->assertEquals('Rider Updated Name', $fresh->name);
        $this->assertEquals('ev', $fresh->vehicle_type);
        $this->assertEquals('KA-05-EV-1234', $fresh->vehicle_number);
        $this->assertEquals('updated@upi', $fresh->upi_id);
    }

    public function test_admin_can_approve_partner_kyc(): void
    {
        $admin = $this->getSuperAdmin();
        $pendingPartner = DeliveryPartner::create([
            'name'            => 'Applicant Partner',
            'email'           => 'applicant@test.com',
            'phone'           => '+91 9888877777',
            'password'        => bcrypt('applicantSecret'),
            'vehicle_type'    => 'bike',
            'vehicle_number'  => 'TN-01-AB-1234',
            'status'          => DeliveryPartner::STATUS_PENDING_APPROVAL,
            'is_available'    => false,
            'location_source' => DeliveryPartner::LOCATION_SOURCE_STATIC,
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.delivery_partners.approve', $pendingPartner));

        $response->assertSessionHas('success');
        $fresh = $pendingPartner->fresh();
        $this->assertEquals(DeliveryPartner::STATUS_ACTIVE, $fresh->status);
        $this->assertNotNull($fresh->approved_at);
        $this->assertNull($fresh->rejection_reason);
    }

    public function test_admin_can_reject_partner_kyc_with_reason(): void
    {
        $admin = $this->getSuperAdmin();
        $pendingPartner = DeliveryPartner::create([
            'name'            => 'Applicant Partner 2',
            'email'           => 'applicant2@test.com',
            'phone'           => '+91 9888877776',
            'password'        => bcrypt('applicantSecret'),
            'vehicle_type'    => 'bike',
            'status'          => DeliveryPartner::STATUS_PENDING_APPROVAL,
            'is_available'    => false,
            'location_source' => DeliveryPartner::LOCATION_SOURCE_STATIC,
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.delivery_partners.reject', $pendingPartner), [
            'rejection_reason' => 'Driving license expired in 2024. Please re-upload current license.',
        ]);

        $response->assertSessionHas('success');
        $fresh = $pendingPartner->fresh();
        $this->assertEquals(DeliveryPartner::STATUS_REJECTED, $fresh->status);
        $this->assertEquals('Driving license expired in 2024. Please re-upload current license.', $fresh->rejection_reason);
        $this->assertFalse($fresh->is_available);
    }

    public function test_admin_can_toggle_partner_status(): void
    {
        $admin = $this->getSuperAdmin();
        $partner = $this->getPartner();
        $this->assertEquals(DeliveryPartner::STATUS_ACTIVE, $partner->status);

        // Suspend
        $response = $this->actingAs($admin, 'admin')->patch(route('admin.delivery_partners.toggle_status', $partner));
        $response->assertSessionHas('success');
        $this->assertEquals(DeliveryPartner::STATUS_SUSPENDED, $partner->fresh()->status);

        // Reactivate
        $response = $this->actingAs($admin, 'admin')->patch(route('admin.delivery_partners.toggle_status', $partner));
        $response->assertSessionHas('success');
        $this->assertEquals(DeliveryPartner::STATUS_ACTIVE, $partner->fresh()->status);
    }

    public function test_admin_can_delete_delivery_partner_without_active_orders(): void
    {
        $admin = $this->getSuperAdmin();
        $partnerToDelete = DeliveryPartner::create([
            'name'            => 'Disposable Partner',
            'email'           => 'disposable@test.com',
            'phone'           => '+91 9888877775',
            'password'        => bcrypt('secretPass123'),
            'vehicle_type'    => 'bike',
            'status'          => DeliveryPartner::STATUS_ACTIVE,
            'is_available'    => false,
            'location_source' => DeliveryPartner::LOCATION_SOURCE_STATIC,
        ]);

        $response = $this->actingAs($admin, 'admin')->delete(route('admin.delivery_partners.destroy', $partnerToDelete));

        $response->assertRedirect(route('admin.delivery_partners.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('delivery_partners', ['id' => $partnerToDelete->id]);
    }

    public function test_admin_cannot_delete_partner_with_active_deliveries(): void
    {
        $admin = $this->getSuperAdmin();
        $partner = $this->getPartner();
        $order = $this->makeOrder(Order::STATUS_OUT_FOR_DELIVERY);
        $order->delivery_partner_id = $partner->id;
        $order->save();

        $response = $this->actingAs($admin, 'admin')->delete(route('admin.delivery_partners.destroy', $partner));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('delivery_partners', ['id' => $partner->id]);
    }

    public function test_delivery_ops_admin_with_partners_permission_can_manage_fleet(): void
    {
        $role = Role::where('slug', 'delivery-ops-manager')->firstOrFail();
        $deliveryAdmin = Admin::create([
            'name'     => 'Delivery Ops Staff',
            'email'    => 'ops@shopy.test',
            'phone'    => '+1999888777',
            'password' => bcrypt('password123'),
            'status'   => Admin::STATUS_ACTIVE,
        ]);
        $deliveryAdmin->roles()->attach($role->id);

        $partner = $this->getPartner();

        $response = $this->actingAs($deliveryAdmin, 'admin')->get(route('admin.delivery_partners.index'));
        $response->assertStatus(200);
        $response->assertSee('Delivery Partners');

        $showResponse = $this->actingAs($deliveryAdmin, 'admin')->get(route('admin.delivery_partners.show', $partner));
        $showResponse->assertStatus(200);

        $createResponse = $this->actingAs($deliveryAdmin, 'admin')->get(route('admin.delivery_partners.create'));
        $createResponse->assertStatus(200);
    }

    public function test_admin_without_permissions_is_forbidden_from_fleet_routes(): void
    {
        $restrictedRole = Role::create([
            'name'        => 'Restricted Role',
            'slug'        => 'restricted-role',
            'description' => 'No delivery or order permissions',
            'status'      => true,
        ]);
        $restrictedAdmin = Admin::create([
            'name'     => 'Restricted Admin',
            'email'    => 'restricted@shopy.test',
            'phone'    => '+1999888776',
            'password' => bcrypt('password123'),
            'status'   => Admin::STATUS_ACTIVE,
        ]);
        $restrictedAdmin->roles()->attach($restrictedRole->id);

        $response = $this->actingAs($restrictedAdmin, 'admin')->get(route('admin.delivery_partners.index'));
        $response->assertStatus(403);
    }
}
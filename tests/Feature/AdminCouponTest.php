<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Mode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCouponTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Admin $superAdmin;
    protected User $customer;
    protected Mode $shopyMode;
    protected Mode $minutesMode;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->admin = Admin::where('email', 'admin@shopy.test')->first();
        $this->superAdmin = Admin::where('email', 'superadmin@shopy.test')->first();
        $this->customer = User::where('email', 'customer@shopy.test')->first();
        $this->shopyMode = Mode::where('slug', 'shopy')->first();
        $this->minutesMode = Mode::where('slug', 'minutes')->first();
    }

    public function test_guest_cannot_access_admin_coupons(): void
    {
        $response = $this->get(route('admin.coupons.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_customer_cannot_access_admin_coupons(): void
    {
        $response = $this->actingAs($this->customer, 'web')
            ->get(route('admin.coupons.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_coupons_index(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.coupons.index'));

        $response->assertStatus(200);
        $response->assertSee('Coupons &amp; Offers', false);
        $response->assertSee('WELCOME50');
        $response->assertSee('SAVE10');
    }

    public function test_admin_can_filter_coupons_by_search(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.coupons.index', ['search' => 'WELCOME50']));

        $response->assertStatus(200);
        $response->assertSee('WELCOME50');
        $response->assertDontSee('MINUTES20');
    }

    public function test_admin_can_filter_coupons_by_type(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.coupons.index', ['type' => 'free_delivery']));

        $response->assertStatus(200);
        $response->assertSee('FREESHIP');
        $response->assertDontSee('WELCOME50');
    }

    public function test_admin_can_view_create_coupon_form(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.coupons.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Promotional Coupon');
        $response->assertSee('Generate Random');
    }

    public function test_admin_can_store_percentage_coupon(): void
    {
        $data = [
            'code' => 'DIWALI25',
            'name' => 'Diwali 25% Off Celebration',
            'description' => 'Festive 25% off all orders up to ₹200 cap',
            'type' => 'percentage',
            'value' => 25,
            'max_discount_amount' => 200,
            'min_order_amount' => 499,
            'mode_id' => $this->shopyMode->id,
            'usage_limit' => 500,
            'usage_limit_per_user' => 1,
            'status' => '1',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.coupons.store'), $data);

        $response->assertRedirect(route('admin.coupons.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('coupons', [
            'code' => 'DIWALI25',
            'name' => 'Diwali 25% Off Celebration',
            'type' => 'percentage',
            'value' => 25.00,
            'max_discount_amount' => 200.00,
            'min_order_amount' => 499.00,
            'status' => true,
        ]);
    }

    public function test_coupon_code_must_be_unique(): void
    {
        $data = [
            'code' => 'WELCOME50', // Already seeded
            'name' => 'Duplicate Code Offer',
            'type' => 'fixed',
            'value' => 50,
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.coupons.store'), $data);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_percentage_coupon_cannot_exceed_100_percent(): void
    {
        $data = [
            'code' => 'SUPER150',
            'name' => 'Impossible Discount',
            'type' => 'percentage',
            'value' => 150,
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.coupons.store'), $data);

        $response->assertSessionHasErrors(['value']);
    }

    public function test_admin_can_view_edit_coupon_form(): void
    {
        $coupon = Coupon::where('code', 'WELCOME50')->first();

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.coupons.edit', $coupon));

        $response->assertStatus(200);
        $response->assertSee('Edit Coupon');
        $response->assertSee('WELCOME50');
    }

    public function test_admin_can_update_coupon(): void
    {
        $coupon = Coupon::where('code', 'WELCOME50')->first();

        $data = [
            'code' => 'WELCOME50',
            'name' => 'Updated Welcome Offer',
            'description' => 'Updated description text',
            'type' => 'fixed',
            'value' => 75,
            'min_order_amount' => 350,
            'status' => '1',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.coupons.update', $coupon), $data);

        $response->assertRedirect(route('admin.coupons.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'name' => 'Updated Welcome Offer',
            'type' => 'fixed',
            'value' => 75.00,
            'min_order_amount' => 350.00,
        ]);
    }

    public function test_admin_can_toggle_coupon_status_via_ajax(): void
    {
        $coupon = Coupon::where('code', 'WELCOME50')->first();
        $this->assertTrue($coupon->status);

        $response = $this->actingAs($this->admin, 'admin')
            ->patchJson(route('admin.coupons.toggleStatus', $coupon));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => false,
        ]);

        $this->assertFalse($coupon->fresh()->status);
    }

    public function test_admin_can_view_coupon_show_analytics(): void
    {
        $coupon = Coupon::where('code', 'WELCOME50')->first();

        // Simulate a usage log
        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id' => $this->customer->id,
            'order_id' => 101,
            'discount_amount' => 50.00,
            'used_at' => now(),
        ]);
        $coupon->increment('times_used');

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.coupons.show', $coupon));

        $response->assertStatus(200);
        $response->assertSee('WELCOME50');
        $response->assertSee($this->customer->name);
        $response->assertSee('₹50.00');
    }

    public function test_admin_can_delete_coupon(): void
    {
        $coupon = Coupon::create([
            'code' => 'TO_DELETE',
            'name' => 'Temporary Promo',
            'type' => 'fixed',
            'value' => 10,
            'status' => true,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.coupons.destroy', $coupon));

        $response->assertRedirect(route('admin.coupons.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('coupons', [
            'code' => 'TO_DELETE',
        ]);
    }
}

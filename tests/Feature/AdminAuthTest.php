<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new DatabaseSeeder())->run();
    }

    public function test_admin_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('Administrator Portal');
        $response->assertSee('toastr.min.js');
        $response->assertSee('toastr.min.css');
        $response->assertSee('toastr.options');
    }

    public function test_authenticated_admin_visiting_login_redirects_to_admin_dashboard(): void
    {
        $admin = Admin::where('email', 'admin@shopy.test')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin, 'admin')->get('/admin/login');

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_admin_can_authenticate_via_admin_login(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@shopy.test',
            'password' => 'password',
        ]);

        $this->assertAuthenticated('admin');
        $response->assertRedirect('/admin/dashboard');
    }

    public function test_super_admin_can_authenticate_via_admin_login(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'superadmin@shopy.test',
            'password' => 'password',
        ]);

        $this->assertAuthenticated('admin');
        $response->assertRedirect('/admin/dashboard');
    }

    public function test_normal_customer_cannot_authenticate_via_admin_login(): void
    {
        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'customer@shopy.test',
            'password' => 'password',
        ]);

        $this->assertGuest('admin');
        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_unauthenticated_user_accessing_admin_dashboard_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/admin/login');
    }

    public function test_normal_customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::where('email', 'customer@shopy.test')->first();
        $this->assertNotNull($customer);

        $response = $this->actingAs($customer, 'web')->get('/admin/dashboard');

        // Customer must be redirected to admin login because they are not authenticated in admin guard
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = Admin::where('email', 'admin@shopy.test')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin, 'admin')->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin Control Center');
        $response->assertSee($admin->name);
    }

    public function test_super_admin_can_access_admin_dashboard(): void
    {
        $superAdmin = Admin::where('email', 'superadmin@shopy.test')->first();
        $this->assertNotNull($superAdmin);

        $response = $this->actingAs($superAdmin, 'admin')->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin Control Center');
    }

    public function test_admin_can_logout(): void
    {
        $admin = Admin::where('email', 'admin@shopy.test')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin, 'admin')->post('/admin/logout');

        $this->assertGuest('admin');
        $response->assertRedirect('/admin/login');
    }
}

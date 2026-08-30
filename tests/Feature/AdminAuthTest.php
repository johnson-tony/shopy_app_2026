<?php

namespace Tests\Feature;

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
    }

    public function test_admin_can_authenticate_via_admin_login(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@shopy.test',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin/dashboard');
    }

    public function test_super_admin_can_authenticate_via_admin_login(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'superadmin@shopy.test',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin/dashboard');
    }

    public function test_normal_customer_cannot_authenticate_via_admin_login(): void
    {
        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'customer@shopy.test',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('error');
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

        $response = $this->actingAs($customer)->get('/admin/dashboard');

        // Customer must be rejected with 403 Forbidden
        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::where('email', 'admin@shopy.test')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin Control Center');
        $response->assertSee($admin->name);
    }

    public function test_super_admin_can_access_admin_dashboard(): void
    {
        $superAdmin = User::where('email', 'superadmin@shopy.test')->first();
        $this->assertNotNull($superAdmin);

        $response = $this->actingAs($superAdmin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin Control Center');
    }

    public function test_admin_can_logout(): void
    {
        $admin = User::where('email', 'admin@shopy.test')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->post('/admin/logout');

        $this->assertGuest();
        $response->assertRedirect('/admin/login');
    }
}

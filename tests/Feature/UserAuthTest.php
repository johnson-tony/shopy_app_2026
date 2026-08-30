<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new DatabaseSeeder())->run();
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Welcome Back');
        $response->assertDontSee('Admin Portal');
        $response->assertDontSee('Are you an administrator?');
        $response->assertSee('toastr.min.js');
        $response->assertSee('toastr.min.css');
        $response->assertSee('toastr.options');
    }

    public function test_register_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Create Account');
    }

    public function test_users_can_register_as_store_customers(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Customer',
            'email' => 'newcustomer@example.com',
            'phone' => '+1555123456',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated('web');
        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'newcustomer@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(User::STATUS_ACTIVE, $user->status);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $response = $this->post('/login', [
            'email' => 'customer@shopy.test',
            'password' => 'password',
        ]);

        $this->assertAuthenticated('web');
        $response->assertRedirect('/dashboard');
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $this->post('/login', [
            'email' => 'customer@shopy.test',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest('web');
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'inactive@shopy.test',
            'password' => 'password',
        ]);

        $this->assertGuest('web');
        $response->assertRedirect('/login');
        $response->assertSessionHas('error');
    }

    public function test_blocked_users_cannot_authenticate(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'blocked@shopy.test',
            'password' => 'password',
        ]);

        $this->assertGuest('web');
        $response->assertRedirect('/login');
        $response->assertSessionHas('error');
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::where('email', 'customer@shopy.test')->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user, 'web')->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Customer Dashboard');
        $response->assertSee($user->name);
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::where('email', 'customer@shopy.test')->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user, 'web')->put('/profile', [
            'name' => 'Alice Customer Updated',
            'email' => 'customer@shopy.test',
            'phone' => '+1999999999',
        ]);

        $response->assertRedirect('/profile');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Alice Customer Updated',
            'phone' => '+1999999999',
        ]);
    }

    public function test_user_can_logout(): void
    {
        $user = User::where('email', 'customer@shopy.test')->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user, 'web')->post('/logout');

        $this->assertGuest('web');
        $response->assertRedirect('/login');
    }
}

<?php

namespace Tests\Feature;

use App\Mail\UserEmailOtpMail;
use App\Models\User;
use App\Models\UserEmailVerification;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
        $response->assertSee('togglePasswordBtn');
        $response->assertSee('togglePasswordIcon');
    }

    public function test_register_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Create Account');
        $response->assertSee('Phone Number');
    }

    public function test_registration_requires_phone_number(): void
    {
        $response = $this->post('/register', [
            'name' => 'No Phone Customer',
            'email' => 'nophone@example.com',
            'phone' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertDatabaseMissing('users', ['email' => 'nophone@example.com']);
    }

    public function test_users_can_register_and_receive_email_otp(): void
    {
        Mail::fake();

        $response = $this->post('/register', [
            'name' => 'Test Customer',
            'email' => 'newcustomer@example.com',
            'phone' => '+1555123456',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // User account is created as pending, not yet authenticated
        $this->assertGuest('web');
        $response->assertRedirect('/verify-otp?email=newcustomer%40example.com');

        $user = User::where('email', 'newcustomer@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(User::STATUS_PENDING, $user->status);
        $this->assertNull($user->email_verified_at);

        // Verification record is generated
        $verification = UserEmailVerification::where('user_id', $user->id)->first();
        $this->assertNotNull($verification);
        $this->assertEquals(6, strlen($verification->otp));
        $this->assertEquals(64, strlen($verification->token));

        // OTP Email was dispatched
        Mail::assertSent(UserEmailOtpMail::class, function ($mail) use ($user, $verification) {
            return $mail->hasTo($user->email)
                && $mail->otp === $verification->otp
                && $mail->token === $verification->token;
        });
    }

    public function test_user_can_verify_otp_and_activate_account(): void
    {
        $user = User::create([
            'name' => 'Pending Customer',
            'email' => 'pending@example.com',
            'phone' => '+1555987654',
            'password' => Hash::make('password123'),
            'status' => User::STATUS_PENDING,
            'email_verified_at' => null,
        ]);

        $verification = UserEmailVerification::generateForUser($user, 15);

        $response = $this->post('/verify-otp', [
            'email' => 'pending@example.com',
            'otp' => $verification->otp,
        ]);

        $this->assertAuthenticated('web');
        $response->assertRedirect('/dashboard');

        $user->refresh();
        $this->assertEquals(User::STATUS_ACTIVE, $user->status);
        $this->assertNotNull($user->email_verified_at);

        $verification->refresh();
        $this->assertNotNull($verification->verified_at);
    }

    public function test_user_cannot_verify_with_invalid_otp(): void
    {
        $user = User::create([
            'name' => 'Pending Customer',
            'email' => 'pending@example.com',
            'phone' => '+1555987654',
            'password' => Hash::make('password123'),
            'status' => User::STATUS_PENDING,
            'email_verified_at' => null,
        ]);

        UserEmailVerification::generateForUser($user, 15);

        $response = $this->post('/verify-otp', [
            'email' => 'pending@example.com',
            'otp' => '000000',
        ]);

        $this->assertGuest('web');
        $response->assertSessionHasErrors('otp');

        $user->refresh();
        $this->assertEquals(User::STATUS_PENDING, $user->status);
        $this->assertNull($user->email_verified_at);
    }

    public function test_user_cannot_verify_with_expired_otp(): void
    {
        $user = User::create([
            'name' => 'Expired Customer',
            'email' => 'expired@example.com',
            'phone' => '+1555987654',
            'password' => Hash::make('password123'),
            'status' => User::STATUS_PENDING,
            'email_verified_at' => null,
        ]);

        $verification = UserEmailVerification::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => '654321',
            'token' => str_repeat('a', 64),
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->post('/verify-otp', [
            'email' => 'expired@example.com',
            'otp' => '654321',
        ]);

        $this->assertGuest('web');
        $response->assertSessionHasErrors('otp');

        $user->refresh();
        $this->assertEquals(User::STATUS_PENDING, $user->status);
    }

    public function test_cross_device_user_can_verify_via_email_link(): void
    {
        $user = User::create([
            'name' => 'Cross Device Customer',
            'email' => 'crossdevice@example.com',
            'phone' => '+1555333444',
            'password' => Hash::make('password123'),
            'status' => User::STATUS_PENDING,
            'email_verified_at' => null,
        ]);

        $verification = UserEmailVerification::generateForUser($user, 15);

        // Access 1-click verification link from a separate session (e.g. mobile phone)
        $response = $this->get("/verify-otp/link/{$verification->token}");

        $this->assertAuthenticated('web');
        $response->assertRedirect('/dashboard');

        $user->refresh();
        $this->assertEquals(User::STATUS_ACTIVE, $user->status);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_cross_device_user_can_verify_by_entering_email_and_otp(): void
    {
        $user = User::create([
            'name' => 'Manual Device Customer',
            'email' => 'manualdevice@example.com',
            'phone' => '+1555333555',
            'password' => Hash::make('password123'),
            'status' => User::STATUS_PENDING,
            'email_verified_at' => null,
        ]);

        $verification = UserEmailVerification::generateForUser($user, 15);

        // Post OTP directly on another device passing email + OTP
        $response = $this->post('/verify-otp', [
            'email' => 'manualdevice@example.com',
            'otp' => $verification->otp,
        ]);

        $this->assertAuthenticated('web');
        $response->assertRedirect('/dashboard');

        $user->refresh();
        $this->assertEquals(User::STATUS_ACTIVE, $user->status);
    }

    public function test_user_can_resend_otp_with_cooldown(): void
    {
        Mail::fake();

        $user = User::create([
            'name' => 'Resend Customer',
            'email' => 'resend@example.com',
            'phone' => '+1555333666',
            'password' => Hash::make('password123'),
            'status' => User::STATUS_PENDING,
            'email_verified_at' => null,
        ]);

        // First verification created 2 minutes ago
        $first = new UserEmailVerification([
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => '111111',
            'token' => str_repeat('b', 64),
            'expires_at' => now()->addMinutes(13),
        ]);
        $first->timestamps = false;
        $first->created_at = now()->subMinutes(2);
        $first->updated_at = now()->subMinutes(2);
        $first->save();

        // Resend request
        $response = $this->post('/verify-otp/resend', [
            'email' => 'resend@example.com',
        ]);

        $response->assertRedirect('/verify-otp?email=resend%40example.com');
        $response->assertSessionHas('success');
        Mail::assertSent(UserEmailOtpMail::class);

        // Immediate subsequent resend should trigger cooldown
        $secondResponse = $this->post('/verify-otp/resend', [
            'email' => 'resend@example.com',
        ]);
        $secondResponse->assertSessionHas('error');
    }

    public function test_pending_user_cannot_login_without_otp_verification(): void
    {
        $user = User::create([
            'name' => 'Unverified Customer',
            'email' => 'unverified@shopy.test',
            'phone' => '+1555333777',
            'password' => Hash::make('password'),
            'status' => User::STATUS_PENDING,
            'email_verified_at' => null,
        ]);

        $response = $this->post('/login', [
            'email' => 'unverified@shopy.test',
            'password' => 'password',
        ]);

        $this->assertGuest('web');
        $response->assertRedirect('/verify-otp?email=unverified%40shopy.test');
        $response->assertSessionHas('info');
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

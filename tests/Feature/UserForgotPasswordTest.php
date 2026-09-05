<?php

namespace Tests\Feature;

use App\Mail\UserResetPasswordOtpMail;
use App\Models\User;
use App\Models\UserPasswordResetOtp;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new DatabaseSeeder())->run();
    }

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('Forgot Password?');
        $response->assertSee('Send 6-Digit Reset Code');
        // Storefront header & footer should be omitted
        $response->assertDontSee('id="siteHeader"', false);
    }

    public function test_user_can_request_password_reset_otp(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->post('/forgot-password', [
            'email' => 'customer@example.com',
        ]);

        // Step 1 redirects to Step 2 (Verify OTP only)
        $response->assertRedirect('/forgot-password/verify?email=customer%40example.com');
        $response->assertSessionHas('success');

        Mail::assertSent(UserResetPasswordOtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo('customer@example.com')
                && strlen($mail->otp) === 6
                && !empty($mail->token);
        });

        $this->assertDatabaseHas('user_password_reset_otps', [
            'user_id' => $user->id,
            'email' => 'customer@example.com',
        ]);
    }

    public function test_non_existent_email_cannot_request_reset(): void
    {
        Mail::fake();

        $response = $this->post('/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertSessionHasErrors('email');
        Mail::assertNothingSent();
    }

    public function test_blocked_user_cannot_request_password_reset(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'blocked@example.com',
            'status' => User::STATUS_BLOCKED,
        ]);

        $response = $this->post('/forgot-password', [
            'email' => 'blocked@example.com',
        ]);

        $response->assertSessionHasErrors('email');
        Mail::assertNothingSent();
    }

    public function test_rate_limit_cooldown_prevents_immediate_resend(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'ratelimit@example.com',
            'status' => User::STATUS_ACTIVE,
        ]);

        // First request
        $this->post('/forgot-password', ['email' => $user->email]);

        // Immediate second request hits the 60-second cooldown
        $secondResponse = $this->post('/forgot-password', ['email' => $user->email]);

        $secondResponse->assertRedirect('/forgot-password/verify?email=ratelimit%40example.com');
        $secondResponse->assertSessionHas('error');

        Mail::assertSent(UserResetPasswordOtpMail::class, 1);
    }

    public function test_step2_verify_otp_screen_can_be_rendered_without_password_inputs(): void
    {
        $response = $this->get('/forgot-password/verify?email=test@example.com');

        $response->assertStatus(200);
        $response->assertSee('Verify Reset Code');
        $response->assertSee('test@example.com');
        $response->assertSee('6-Digit Reset Code');
        $response->assertSee('Verify Code &amp; Proceed', false);
        // Ensure password fields are NOT on this screen
        $response->assertDontSee('toggleResetPasswordBtn');
        $response->assertDontSee('Confirm New Password');
    }

    public function test_step2_user_can_verify_otp_and_is_redirected_to_new_password_form(): void
    {
        $user = User::factory()->create([
            'email' => 'step2user@example.com',
            'status' => User::STATUS_ACTIVE,
        ]);

        $reset = UserPasswordResetOtp::generateForUser($user, 15);

        $response = $this->post('/forgot-password/verify', [
            'email' => 'step2user@example.com',
            'otp'   => $reset->otp,
        ]);

        // Should redirect to Step 3: Set New Password with the token
        $response->assertRedirect('/reset-password/new?token=' . $reset->token);
        $response->assertSessionHas('password_reset_verified_token', $reset->token);
    }

    public function test_step2_user_cannot_verify_with_invalid_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'wrongotp@example.com',
            'status' => User::STATUS_ACTIVE,
        ]);

        UserPasswordResetOtp::generateForUser($user, 15);

        $response = $this->from('/forgot-password/verify')->post('/forgot-password/verify', [
            'email' => 'wrongotp@example.com',
            'otp'   => '999999',
        ]);

        $response->assertSessionHasErrors('otp');
    }

    public function test_step3_new_password_screen_can_be_rendered_only_after_verification(): void
    {
        $user = User::factory()->create([
            'email' => 'verifieduser@example.com',
            'status' => User::STATUS_ACTIVE,
        ]);

        $reset = UserPasswordResetOtp::generateForUser($user, 15);

        $response = $this->get('/reset-password/new?token=' . $reset->token);

        $response->assertStatus(200);
        $response->assertSee('Set New Password');
        $response->assertSee('verifieduser@example.com');
        $response->assertSee('toggleResetPasswordBtn');
        $response->assertSee('toggleConfirmResetPasswordBtn');
        // Ensure 6-digit OTP input boxes are NOT on this screen
        $response->assertDontSee('6-Digit Reset Code');
    }

    public function test_step3_user_can_set_new_password_after_verification(): void
    {
        $user = User::factory()->create([
            'email' => 'validotp@example.com',
            'password' => Hash::make('oldpassword123'),
            'status' => User::STATUS_ACTIVE,
        ]);

        $reset = UserPasswordResetOtp::generateForUser($user, 15);

        // Submit new password with the verified token
        $response = $this->post('/reset-password', [
            'token' => $reset->token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        // User should be redirected to login with prefilled email, NOT auto-logged in
        $response->assertRedirect('/login?email=validotp%40example.com');
        $response->assertSessionHas('success');
        $this->assertGuest('web');

        // Verify password in DB has been updated
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));

        // Verify OTP is marked as used
        $reset->refresh();
        $this->assertTrue($reset->isUsed());
    }

    public function test_cross_device_user_can_reset_password_via_email_link(): void
    {
        $user = User::factory()->create([
            'email' => 'crossdevice@example.com',
            'password' => Hash::make('oldpassword123'),
            'status' => User::STATUS_ACTIVE,
        ]);

        $reset = UserPasswordResetOtp::generateForUser($user, 15);

        // 1-Click link redirects directly to Step 3: Set New Password
        $linkResponse = $this->get('/reset-password/link/' . $reset->token);
        $linkResponse->assertRedirect('/reset-password/new?token=' . $reset->token);

        // Follow redirect to Step 3
        $step3Response = $this->get('/reset-password/new?token=' . $reset->token);
        $step3Response->assertStatus(200);
        $step3Response->assertSee('Set New Password');
        $step3Response->assertSee('crossdevice@example.com');

        // Submit new password
        $resetResponse = $this->post('/reset-password', [
            'token' => $reset->token,
            'password' => 'brandnewpassword2026',
            'password_confirmation' => 'brandnewpassword2026',
        ]);

        $resetResponse->assertRedirect('/login?email=crossdevice%40example.com');
        $resetResponse->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('brandnewpassword2026', $user->password));
    }

    public function test_user_can_login_with_new_password_after_reset(): void
    {
        $user = User::factory()->create([
            'email' => 'loginuser@example.com',
            'password' => Hash::make('initialpass123'),
            'status' => User::STATUS_ACTIVE,
        ]);

        $reset = UserPasswordResetOtp::generateForUser($user, 15);

        $this->post('/reset-password', [
            'token' => $reset->token,
            'password' => 'updatedsecret789',
            'password_confirmation' => 'updatedsecret789',
        ]);

        // Attempt login with old password fails
        $failLogin = $this->post('/login', [
            'email' => 'loginuser@example.com',
            'password' => 'initialpass123',
        ]);
        $failLogin->assertSessionHasErrors('email');
        $this->assertGuest('web');

        // Attempt login with new password succeeds
        $successLogin = $this->post('/login', [
            'email' => 'loginuser@example.com',
            'password' => 'updatedsecret789',
        ]);
        $successLogin->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_pending_user_is_activated_upon_successful_password_reset(): void
    {
        $user = User::factory()->create([
            'email' => 'pendinguser@example.com',
            'password' => Hash::make('oldsecret123'),
            'status' => User::STATUS_PENDING,
            'email_verified_at' => null,
        ]);

        $reset = UserPasswordResetOtp::generateForUser($user, 15);

        $this->post('/reset-password', [
            'token' => $reset->token,
            'password' => 'newsecretpassword123',
            'password_confirmation' => 'newsecretpassword123',
        ]);

        $user->refresh();
        $this->assertTrue($user->isActive());
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_pending_unused_otp_redirects_user_directly_to_otp_page_when_visiting_forgot_password(): void
    {
        $user = User::factory()->create([
            'email' => 'pendingotp@example.com',
            'status' => User::STATUS_ACTIVE,
        ]);

        // Request OTP in step 1
        $this->post('/forgot-password', ['email' => $user->email]);

        // User revisits /forgot-password with active pending OTP in session
        $response = $this->get('/forgot-password');

        // Automatically resumes at the OTP verification page
        $response->assertRedirect('/forgot-password/verify?email=pendingotp%40example.com');
        $response->assertSessionHas('info');
    }

    public function test_user_can_switch_email_by_clicking_change_email(): void
    {
        $user = User::factory()->create([
            'email' => 'wrongemail@example.com',
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->post('/forgot-password', ['email' => $user->email]);

        // Clicking change email allows starting fresh
        $response = $this->get('/forgot-password?change_email=1');

        $response->assertStatus(200);
        $response->assertSee('Forgot Password?');
    }

    public function test_resending_otp_invalidates_previous_code_so_only_latest_otp_works(): void
    {
        $user = User::factory()->create([
            'email' => 'latestonly@example.com',
            'status' => User::STATUS_ACTIVE,
        ]);

        // First OTP generated
        $firstReset = UserPasswordResetOtp::generateForUser($user, 15);
        $firstOtp = $firstReset->otp;

        // Second OTP generated (e.g. after cooldown / resend)
        $secondReset = UserPasswordResetOtp::generateForUser($user, 15);
        $secondOtp = $secondReset->otp;

        // Previous OTP is expired
        $firstReset->refresh();
        $this->assertTrue($firstReset->isExpired());

        // Attempting verification with old OTP fails
        $failResponse = $this->from('/forgot-password/verify')->post('/forgot-password/verify', [
            'email' => $user->email,
            'otp'   => $firstOtp,
        ]);
        $failResponse->assertSessionHasErrors('otp');

        // Verification with latest OTP succeeds
        $successResponse = $this->post('/forgot-password/verify', [
            'email' => $user->email,
            'otp'   => $secondOtp,
        ]);
        $successResponse->assertRedirect('/reset-password/new?token=' . $secondReset->token);
    }

    public function test_accessing_new_password_form_without_token_redirects_to_request(): void
    {
        $response = $this->get('/reset-password/new');

        $response->assertRedirect('/forgot-password');
        $response->assertSessionHas('error');
    }
}


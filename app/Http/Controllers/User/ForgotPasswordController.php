<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\UserResetPasswordOtpMail;
use App\Models\User;
use App\Models\UserPasswordResetOtp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    /**
     * Step 1: Display the form to request a password reset OTP.
     */
    public function showLinkRequestForm(Request $request): View|RedirectResponse
    {
        if (Auth::guard('web')->check() && Auth::guard('web')->user()->isActive()) {
            return redirect()->route('dashboard');
        }

        if ($request->query('change_email')) {
            $request->session()->forget('password_reset_email');
            $request->session()->forget('password_reset_verified_token');
        } else {
            $email = $request->session()->get('password_reset_email');
            if ($email) {
                $user = User::where('email', $email)->first();
                if ($user) {
                    $latest = UserPasswordResetOtp::where('user_id', $user->id)
                        ->whereNull('used_at')
                        ->latest()
                        ->first();

                    if ($latest && !$latest->isExpired()) {
                        return redirect()->route('password.otp', ['email' => $email])
                            ->with('info', 'You already have an active reset code. Please enter it below or wait for cooldown to resend.');
                    }
                }
            }
        }

        return view('user.pages.auth.forgot-password');
    }

    /**
     * Step 1 Submit: Send a password reset OTP to the user's email.
     */
    public function sendResetOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'exists:users,email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'We could not find an account with that email address.']);
        }

        if ($user->status === User::STATUS_BLOCKED) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Your account is blocked. Please contact support.']);
        }

        // Rate limit: 60 seconds cooldown
        $latest = UserPasswordResetOtp::where('user_id', $user->id)->latest()->first();
        if ($latest && $latest->created_at && $latest->created_at->diffInSeconds(now()) < 60) {
            $waitSeconds = 60 - $latest->created_at->diffInSeconds(now());
            return redirect()->route('password.otp', ['email' => $user->email])
                ->with('error', "Please wait {$waitSeconds} seconds before requesting a new code.");
        }

        $resetRecord = UserPasswordResetOtp::generateForUser($user, 15);

        try {
            Mail::to($user->email)->send(new UserResetPasswordOtpMail($user, $resetRecord->otp, $resetRecord->token));
        } catch (\Throwable $e) {
            Log::error('Failed to send password reset OTP: ' . $e->getMessage());
        }

        $request->session()->put('password_reset_email', $user->email);

        return redirect()->route('password.otp', ['email' => $user->email])
            ->with('success', "A 6-digit password reset code has been sent to {$user->email}. Please check your email.");
    }

    /**
     * Step 2: Display the 6-digit OTP verification form only.
     */
    public function showVerifyOtpForm(Request $request): View|RedirectResponse
    {
        if (Auth::guard('web')->check() && Auth::guard('web')->user()->isActive()) {
            return redirect()->route('dashboard');
        }

        $email = $request->query('email') ?? $request->session()->get('password_reset_email');

        return view('user.pages.auth.verify-reset-otp', compact('email'));
    }

    /**
     * Step 2 Submit: Verify the entered 6-digit OTP code.
     */
    public function verifyResetOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'exists:users,email'],
            'otp'   => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'User account not found.']);
        }

        $record = UserPasswordResetOtp::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (!$record || !$record->isValid($validated['otp'])) {
            return back()->withInput($request->only('email'))
                ->withErrors(['otp' => 'Invalid or expired 6-digit reset code. Please check and try again.']);
        }

        // Store authorized token in session and redirect to Step 3 (Set New Password)
        $request->session()->put('password_reset_verified_token', $record->token);
        $request->session()->put('password_reset_email', $user->email);

        return redirect()->route('password.reset_new', ['token' => $record->token])
            ->with('success', 'Reset code verified! Please choose your new password.');
    }

    /**
     * Step 3: Display the Set New Password form (only after OTP or 1-click token is verified).
     */
    public function showNewPasswordForm(Request $request): View|RedirectResponse
    {
        if (Auth::guard('web')->check() && Auth::guard('web')->user()->isActive()) {
            return redirect()->route('dashboard');
        }

        $token = $request->query('token') ?? $request->session()->get('password_reset_verified_token');

        if (empty($token)) {
            return redirect()->route('password.request')
                ->with('error', 'Please request a password reset code first.');
        }

        $record = UserPasswordResetOtp::where('token', $token)->first();

        if (!$record || $record->isExpired() || $record->isUsed()) {
            return redirect()->route('password.request')
                ->with('error', 'This password reset link has expired or has already been used. Please request a new code.');
        }

        $email = $record->email;

        return view('user.pages.auth.reset-password', compact('token', 'email'));
    }

    /**
     * Alias / Fallback for /reset-password route.
     */
    public function showResetForm(Request $request): RedirectResponse
    {
        $token = $request->query('token') ?? $request->session()->get('password_reset_verified_token');

        if (!empty($token)) {
            return redirect()->route('password.reset_new', ['token' => $token]);
        }

        $email = $request->query('email') ?? $request->session()->get('password_reset_email');
        return redirect()->route('password.otp', array_filter(['email' => $email]));
    }

    /**
     * Handle cross-device 1-click password reset link from email.
     */
    public function showResetFormWithToken(string $token): RedirectResponse
    {
        $resetRecord = UserPasswordResetOtp::where('token', $token)->first();

        if (!$resetRecord) {
            return redirect()->route('password.request')
                ->with('error', 'Invalid password reset link.');
        }

        if ($resetRecord->isExpired() || $resetRecord->isUsed()) {
            return redirect()->route('password.request')
                ->with('error', 'This password reset link has expired or has already been used. Please request a new one.');
        }

        session()->put('password_reset_verified_token', $token);
        session()->put('password_reset_email', $resetRecord->email);

        return redirect()->route('password.reset_new', ['token' => $token])
            ->with('success', 'Email verified via secure link! Please choose your new password.');
    }

    /**
     * Step 3 Submit: Reset the user's password once verified.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'token'    => ['nullable', 'string', 'size:64'],
            'email'    => ['nullable', 'string', 'email', 'max:255', 'exists:users,email'],
            'otp'      => ['nullable', 'string', 'size:6'],
        ]);

        $record = null;

        if (!empty($validated['token'])) {
            $record = UserPasswordResetOtp::where('token', $validated['token'])->first();
        } elseif (!empty($validated['email']) && !empty($validated['otp'])) {
            $user = User::where('email', $validated['email'])->first();
            if ($user) {
                $record = UserPasswordResetOtp::where('user_id', $user->id)
                    ->whereNull('used_at')
                    ->latest()
                    ->first();

                if ($record && !hash_equals($record->otp, trim($validated['otp']))) {
                    return back()->withInput($request->only('email'))
                        ->withErrors(['otp' => 'Invalid 6-digit reset code. Please check and try again.']);
                }
            }
        } elseif ($request->session()->has('password_reset_verified_token')) {
            $sessionToken = $request->session()->get('password_reset_verified_token');
            $record = UserPasswordResetOtp::where('token', $sessionToken)->first();
        }

        if (!$record || $record->isExpired() || $record->isUsed()) {
            return redirect()->route('password.request')
                ->with('error', 'This reset session has expired or has already been used. Please request a new code.');
        }

        $user = $record->user;
        if (!$user) {
            return redirect()->route('password.request')
                ->with('error', 'User account not found.');
        }

        // Update password
        $user->password = Hash::make($validated['password']);
        if ($user->isPending()) {
            $user->status = User::STATUS_ACTIVE;
            $user->email_verified_at = now();
        }
        $user->save();

        $record->markAsUsed();

        $request->session()->forget([
            'password_reset_email',
            'password_reset_verified_token',
        ]);

        return redirect()->route('login', ['email' => $user->email])
            ->with('success', 'Your password has been reset successfully! Please sign in with your new password.');
    }

    /**
     * Resend password reset OTP code with cooldown.
     */
    public function resendResetOtp(Request $request): RedirectResponse
    {
        $email = $request->input('email') ?? $request->session()->get('password_reset_email');

        if (empty($email)) {
            return redirect()->route('password.request')->withErrors(['email' => 'Please provide your email address.']);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found for this email address.']);
        }

        // Rate limit: 60 seconds cooldown
        $latest = UserPasswordResetOtp::where('user_id', $user->id)->latest()->first();
        if ($latest && $latest->created_at && $latest->created_at->diffInSeconds(now()) < 60) {
            $waitSeconds = 60 - $latest->created_at->diffInSeconds(now());
            return redirect()->route('password.otp', ['email' => $user->email])
                ->with('error', "Please wait {$waitSeconds} seconds before requesting a new code.");
        }

        $resetRecord = UserPasswordResetOtp::generateForUser($user, 15);

        try {
            Mail::to($user->email)->send(new UserResetPasswordOtpMail($user, $resetRecord->otp, $resetRecord->token));
        } catch (\Throwable $e) {
            Log::error('Failed to resend password reset OTP: ' . $e->getMessage());
        }

        $request->session()->put('password_reset_email', $user->email);

        return redirect()->route('password.otp', ['email' => $user->email])
            ->with('success', 'A new 6-digit password reset code has been sent to your email.');
    }
}

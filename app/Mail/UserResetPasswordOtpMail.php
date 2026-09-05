<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserResetPasswordOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $otp;
    public string $token;
    public string $resetUrl;
    public int $expiryMinutes;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $otp, string $token, int $expiryMinutes = 15)
    {
        $this->user = $user;
        $this->otp = $otp;
        $this->token = $token;
        $this->expiryMinutes = $expiryMinutes;
        $this->resetUrl = route('password.reset_link', ['token' => $token]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Shopy Password Reset Code: {$this->otp}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user-reset-password-otp',
        );
    }
}

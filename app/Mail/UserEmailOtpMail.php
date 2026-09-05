<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserEmailOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $otp;
    public string $token;
    public string $verifyUrl;
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
        $this->verifyUrl = route('verification.verify_link', ['token' => $token]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Shopy Verification Code: {$this->otp}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user-otp',
        );
    }
}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Shopy Account</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f172a; color: #f8fafc; padding: 40px 20px; margin: 0;">
    <div style="max-width: 560px; margin: 0 auto; background-color: #1e293b; border-radius: 16px; border: 1px solid #334155; padding: 32px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
        <!-- Logo Header -->
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="display: inline-block; padding: 12px 20px; border-radius: 12px; background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.3);">
                <span style="font-size: 24px; font-weight: 800; color: #818cf8; letter-spacing: -0.5px;">SHOPY 2026</span>
            </div>
            <h2 style="font-size: 20px; font-weight: 700; color: #ffffff; margin-top: 16px; margin-bottom: 6px;">Confirm Your Email Address</h2>
            <p style="font-size: 13px; color: #94a3b8; margin: 0;">Complete your registration by entering this 6-digit code</p>
        </div>

        <!-- Main Card -->
        <div style="background-color: #0f172a; border-radius: 14px; padding: 24px; border: 1px solid #334155; margin-bottom: 24px;">
            <p style="font-size: 14px; color: #cbd5e1; margin: 0 0 12px 0;">Hello <strong>{{ $user->name }}</strong>,</p>
            <p style="font-size: 13px; color: #94a3b8; line-height: 1.6; margin: 0 0 20px 0;">
                Thank you for creating an account with Shopy. Use the verification code below to verify your email address (<span style="color: #818cf8; font-family: monospace;">{{ $user->email }}</span>).
            </p>

            <!-- 6-digit OTP Box -->
            <div style="text-align: center; margin: 24px 0;">
                <div style="display: inline-block; background: #1e293b; border: 2px dashed #6366f1; border-radius: 12px; padding: 16px 32px;">
                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #a5b4fc; font-weight: 600; margin-bottom: 6px;">Your 6-Digit Verification Code</div>
                    <div style="font-size: 34px; font-weight: 800; letter-spacing: 8px; color: #ffffff; font-family: 'Courier New', Courier, monospace;">
                        {{ $otp }}
                    </div>
                </div>
            </div>

            <!-- Multi-device note -->
            <div style="background: rgba(99, 102, 241, 0.08); border-left: 3px solid #6366f1; padding: 12px 14px; border-radius: 0 8px 8px 0; margin-bottom: 20px;">
                <p style="font-size: 12px; color: #cbd5e1; line-height: 1.5; margin: 0;">
                    📱 <strong>Multi-Device Friendly:</strong><br>
                    • If you registered on a <strong>laptop / PC</strong>, simply keep that window open and enter the 6-digit code shown above.<br>
                    • If you are viewing this on your <strong>mobile phone</strong>, you can also tap the button below to verify your account in one click!
                </p>
            </div>

            <!-- One-Click Cross-Device Link Button -->
            <div style="text-align: center; margin: 20px 0;">
                <a href="{{ $verifyUrl }}" target="_blank" rel="noopener noreferrer" style="display: inline-block; background-color: #4f46e5; color: #ffffff; font-size: 14px; font-weight: 600; text-decoration: none; padding: 12px 28px; border-radius: 10px; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);">
                    Verify Account in 1-Click &rarr;
                </a>
            </div>

            <p style="font-size: 12px; color: #64748b; margin: 16px 0 0 0; text-align: center;">
                This code expires in <strong>{{ $expiryMinutes }} minutes</strong>. If you did not sign up for Shopy, you can disregard this email.
            </p>
        </div>

        <!-- Footer -->
        <div style="border-top: 1px solid #334155; padding-top: 16px; font-size: 11px; color: #64748b; text-align: center; line-height: 1.5;">
            <p style="margin: 0 0 6px 0;">This is an automated system email from Shopy. Please do not reply directly.</p>
            <p style="margin: 0;">&copy; {{ date('Y') }} Shopy Platform. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

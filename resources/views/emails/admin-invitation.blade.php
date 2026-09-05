<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Administrator Invitation — Shopy</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f172a; color: #f8fafc; padding: 40px 20px; margin: 0;">
    <div style="max-width: 560px; margin: 0 auto; background-color: #1e293b; border-radius: 16px; border: 1px solid #334155; padding: 32px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="display: inline-block; padding: 12px; border-radius: 12px; background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.3);">
                <span style="font-size: 24px; font-weight: bold; color: #818cf8; letter-spacing: -0.5px;">SHOPY</span>
            </div>
            <h2 style="font-size: 20px; font-weight: 700; color: #ffffff; margin-top: 16px; margin-bottom: 8px;">Administrator Invitation</h2>
            <p style="font-size: 14px; color: #94a3b8; margin: 0;">You have been invited to join the Shopy Administration Panel.</p>
        </div>

        <div style="background-color: #0f172a; border-radius: 12px; padding: 20px; border: 1px solid #334155; margin-bottom: 24px;">
            <p style="font-size: 14px; color: #cbd5e1; margin: 0 0 10px 0;">Hello <strong>{{ $invitation->name }}</strong>,</p>
            <p style="font-size: 13px; color: #94a3b8; line-height: 1.6; margin: 0 0 16px 0;">
                An administrator account has been provisioned for your email (<code style="color: #818cf8; font-family: monospace;">{{ $invitation->email }}</code>).
                To complete your setup and activate your account, please click the secure link below to set your confidential password.
            </p>
            <div style="text-align: center; margin: 24px 0;">
                <a href="{{ $acceptUrl }}" style="display: inline-block; background-color: #4f46e5; color: #ffffff; font-size: 14px; font-weight: 600; text-decoration: none; padding: 12px 28px; border-radius: 10px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);">
                    Set Your Password &amp; Activate Account
                </a>
            </div>
            <p style="font-size: 12px; color: #64748b; margin: 0; text-align: center;">
                This invitation link is single-use and will expire in <strong>48 hours</strong> ({{ $invitation->expires_at->toFormattedDateString() }}).
            </p>
        </div>

        <div style="border-top: 1px solid #334155; padding-top: 16px; font-size: 11px; color: #64748b; text-align: center; line-height: 1.5;">
            <p style="margin: 0 0 6px 0;">If you did not expect this invitation, you can safely ignore this email.</p>
            <p style="margin: 0;">&copy; {{ date('Y') }} Shopy Platform. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

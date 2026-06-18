<!DOCTYPE html>
<html>
<head>
    <title>Account Status Update</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { margin: 0; padding: 0; background-color: #f7fafc; font-family: 'Inter', sans-serif; }
        .container { max-width: 600px; margin: 40px auto; background: white; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.05); }
        .header { padding: 40px 32px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 16px 16px 0 0; color: white; }
        .content { padding: 40px 32px; color: #1e293b; }
        .status-card { background: #f8fafc; border-radius: 12px; padding: 24px; margin: 24px 0; text-align: center; }
        .status-icon { width: 64px; height: 64px; margin: 0 auto 20px; }
        .status-text { font-size: 20px; font-weight: 600; margin: 8px 0; }
        .cta-button { display: inline-block; padding: 16px 32px; background: #6366f1; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: transform 0.2s; }
        .cta-button:hover { transform: translateY(-2px); }
        .footer { padding: 32px; text-align: center; color: #64748b; font-size: 14px; border-top: 1px solid #f1f5f9; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0; font-weight: 700; font-size: 28px;">Account Update</h1>
            <p style="opacity: 0.9; margin: 8px 0 0;">{{ config('app.name') }} Security Team</p>
        </div>

        <div class="content">
            <p style="font-size: 16px; line-height: 1.6;">Hello {{ $user->name }},</p>
            <p style="font-size: 16px; line-height: 1.6;">We wanted to inform you about important changes to your account status:</p>

            <div class="status-card">
                @if($status === 'banned')
                    <svg class="status-icon" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm4-10.9L14.9 12l2.1 2.1-1.4 1.4-2.1-2.1-2.1 2.1-1.4-1.4 2.1-2.1-2.1-2.1 1.4-1.4 2.1 2.1 2.1-2.1 1.4 1.4z"/>
                    </svg>
                    <h2 class="status-text" style="color: #ef4444;">Account Restricted</h2>
                    <p style="color: #64748b;">Your access has been temporarily suspended. Please contact our support team to resolve this matter.</p>
                    <a href="https://t.me/chokesmscc" class="cta-button" style="background: #ef4444; margin-top: 16px;">Contact Support</a>
                @else
                    <svg class="status-icon" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <h2 class="status-text" style="color: #10b981;">Account Restored</h2>
                    <p style="color: #64748b;">Full access to your account has been reinstated. Welcome back!</p>
                @endif
            </div>

            <p style="font-size: 16px; line-height: 1.6; margin-top: 32px;">Need help? Reply to this email or visit our <a href="{{ config('app.help_center') }}" style="color: #6366f1; text-decoration: none; font-weight: 600;">Help Center</a></p>
        </div>

        <div class="footer">
            <p style="margin: 0;">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p style="margin: 8px 0 0; font-size: 13px;">
                <a href="{{ config('app.privacy_policy') }}" style="color: #64748b; text-decoration: none;">Privacy Policy</a> 
                | 
                <a href="{{ config('app.terms_of_service') }}" style="color: #64748b; text-decoration: none;">Terms of Service</a>
            </p>
        </div>
    </div>
</body>
</html>
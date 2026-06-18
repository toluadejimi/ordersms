<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Wallet Credited</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #111827;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding: 40px 0; background-color: #f3f4f6;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #059669; padding: 24px; text-align: center;">
                            <h1 style="color: #ffffff; font-size: 22px; margin: 0;">✅ Wallet Credited by Admin</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 32px;">
                            <p style="font-size: 16px; margin-bottom: 20px;">Hello <strong>{{ $user->name }}</strong>,</p>
                            
                            <p style="font-size: 16px; margin-bottom: 20px;">
                                We’re writing to inform you that your wallet has been manually credited by an administrator.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 24px 0; border: 1px solid #e5e7eb; border-radius: 8px;">
                                <tr style="background-color: #f9fafb;">
                                    <td style="padding: 16px; font-weight: bold; border-bottom: 1px solid #e5e7eb;">Amount Credited</td>
                                    <td style="padding: 16px; border-bottom: 1px solid #e5e7eb;">₦{{ number_format($amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px; font-weight: bold;">New Wallet Balance</td>
                                    <td style="padding: 16px;">₦{{ number_format($balance, 2) }}</td>
                                </tr>
                            </table>

                            <p style="font-size: 15px; margin-bottom: 24px;">
                                If you did not request this credit or believe it was made in error, please contact our support team immediately.
                            </p>

                            <div style="text-align: center;">
                                <a href="{{ url('/dashboard') }}" style="background-color: #059669; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-size: 16px; display: inline-block;">
                                    View Wallet
                                </a>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px; text-align: center; background-color: #f9fafb; font-size: 13px; color: #6b7280;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
                            This message was sent automatically. Please do not reply to this email.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>

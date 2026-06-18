<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Wallet Debited</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f9fafb; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1f2937;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding: 40px 0; background-color: #f9fafb;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #dc2626; padding: 24px; text-align: center;">
                            <h1 style="color: #ffffff; font-size: 22px; margin: 0;">⚠️ Wallet Debited</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 32px;">
                            <p style="font-size: 16px; margin-bottom: 20px;">Hello <strong>{{ $user->name }}</strong>,</p>

                            <p style="font-size: 16px; margin-bottom: 20px;">
                                We want to inform you that your wallet has been <strong>debited by an administrator</strong>.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 24px 0; border: 1px solid #e5e7eb; border-radius: 8px;">
                                <tr style="background-color: #f3f4f6;">
                                    <td style="padding: 16px; font-weight: bold; border-bottom: 1px solid #e5e7eb;">Amount Debited</td>
                                    <td style="padding: 16px; border-bottom: 1px solid #e5e7eb;">₦{{ number_format($amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px; font-weight: bold;">Remaining Balance</td>
                                    <td style="padding: 16px;">₦{{ number_format($balance, 2) }}</td>
                                </tr>
                            </table>

                            <p style="font-size: 15px; margin-bottom: 30px;">
                                If you have any concerns or did not authorize this debit, please contact our support team immediately.
                            </p>

                            <div style="text-align: center;">
                                <a href="{{ url('/dashboard') }}" style="background-color: #dc2626; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-size: 16px; display: inline-block;">
                                    Review Wallet Activity
                                </a>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px; text-align: center; background-color: #f3f4f6; font-size: 13px; color: #6b7280;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
                            This is an automated notification. Please do not reply to this message.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>

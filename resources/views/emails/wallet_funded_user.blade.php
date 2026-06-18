<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Wallet Funded</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333333;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 14px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background-color: #4F46E5; padding: 30px; text-align: center;">
                            <h1 style="margin: 0; font-size: 24px; color: #ffffff;">🎉 Wallet Funded Successfully</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 30px;">
                            <p style="font-size: 16px; margin-bottom: 20px;">Hi there,</p>
                            <p style="font-size: 16px; margin-bottom: 20px;">
                                We're excited to inform you that your wallet has just been funded successfully!
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0; border: 1px solid #e5e7eb; border-radius: 6px;">
                                <tr style="background-color: #f9fafb;">
                                    <td style="padding: 16px; font-weight: bold; border-bottom: 1px solid #e5e7eb;">Amount</td>
                                    <td style="padding: 16px; border-bottom: 1px solid #e5e7eb;">₦{{ number_format($amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px; font-weight: bold; border-bottom: 1px solid #e5e7eb;">Method</td>
                                    <td style="padding: 16px; border-bottom: 1px solid #e5e7eb;">{{ ucfirst($method) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px; font-weight: bold;">Date</td>
                                    <td style="padding: 16px;">{{ now()->format('d M Y, h:i A') }}</td>
                                </tr>
                            </table>

                            <p style="font-size: 16px; margin-bottom: 30px;">You can now use your updated wallet balance to enjoy all our services without interruption.</p>

                            <div style="text-align: center;">
                                <a href="{{ url('/dashboard') }}" style="background-color: #4F46E5; color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; display: inline-block; font-size: 16px;">Go to Dashboard</a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 20px; text-align: center; background-color: #f9fafb; font-size: 13px; color: #6b7280;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
                            This is an automated message. Do not reply to this email.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

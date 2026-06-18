<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manual Funding Submitted</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1f2937;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding: 40px 0; background-color: #f3f4f6;">
        <tr>
            <td align="center">
                <table cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08); overflow: hidden;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #1d4ed8; padding: 24px; text-align: center;">
                            <h1 style="margin: 0; font-size: 22px; color: #ffffff;">🚨 Manual Funding Submitted</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 32px;">
                            <p style="font-size: 16px; margin-bottom: 16px;">Hi Admin,</p>
                            <p style="font-size: 15px; margin-bottom: 24px;">
                                A user has just submitted a manual funding request. Here are the details:
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 24px; border: 1px solid #e5e7eb; border-radius: 8px;">
                                <tr style="background-color: #f9fafb;">
                                    <td style="padding: 16px; font-weight: 600; color: #374151;">User</td>
                                    <td style="padding: 16px;">{{ $user->name }} ({{ $user->email }})</td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px; font-weight: 600; color: #374151;">Amount</td>
                                    <td style="padding: 16px; color: #16a34a; font-weight: bold;">₦{{ number_format($amount, 2) }}</td>
                                </tr>
                                @if(!empty($note))
                                <tr>
                                    <td style="padding: 16px; font-weight: 600; color: #374151;">Note</td>
                                    <td style="padding: 16px;">{{ $note }}</td>
                                </tr>
                                @endif
                            </table>

                            <p style="font-size: 15px; margin-bottom: 32px;">
                                Please log in to the admin dashboard to review, approve, or reject this request.
                            </p>

                            <div style="text-align: center;">
                                <a href="{{ route('admin.manual-fundings.index') }}"
                                   style="background-color: #1d4ed8; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-size: 15px; display: inline-block;">
                                    View Request in Dashboard
                                </a>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px; background-color: #f9fafb; text-align: center; font-size: 13px; color: #6b7280;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>

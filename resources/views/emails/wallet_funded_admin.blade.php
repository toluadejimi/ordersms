<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Wallet Funded</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9fafb;
            color: #1f2937;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        h2 {
            color: #111827;
            font-size: 24px;
            margin-bottom: 20px;
        }
        p {
            margin: 8px 0;
            font-size: 15px;
        }
        .label {
            font-weight: 600;
            color: #4b5563;
        }
        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📥 Wallet Funded</h2>
        <p>A user has successfully funded their wallet. Here are the details:</p>

        <p><span class="label">👤 Name:</span> {{ $user->name }}</p>
        <p><span class="label">📧 Email:</span> {{ $user->email }}</p>
        <p><span class="label">💰 Amount:</span> ₦{{ number_format($amount, 2) }}</p>
        <p><span class="label">💳 Method:</span> {{ ucfirst($method) }}</p>

        <div class="footer">
            This is an automated admin alert from your system.
        </div>
    </div>
</body>
</html>

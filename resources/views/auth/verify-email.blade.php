<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-tr from-indigo-100 via-white to-purple-100 flex items-center justify-center px-4">
    <div class="max-w-lg w-full bg-white p-8 rounded-3xl shadow-2xl border border-gray-200">
        <div class="text-center">
            <svg class="mx-auto mb-4 h-16 w-16 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.5"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.5 12.75L12 17.25m0 0l-4.5-4.5M12 17.25V6.75"/>
            </svg>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Verify Your Email</h2>
            <p class="text-gray-600 mb-6">
                We’ve sent a verification link to your email. Please check your inbox or spam folder and report not spam  to complete your registration.
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4 shadow-sm text-sm text-center">
                ✅ A new verification link has been sent to your email. kindly check your spam folder if you didn't see it
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="space-y-3">
            @csrf
            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-3 rounded-xl hover:bg-indigo-700 transition-all font-semibold shadow">
                Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-6 text-center">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-indigo-600 transition underline">
                ← Log Out
            </button>
        </form>
    </div>
</body>
</html>

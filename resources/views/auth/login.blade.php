<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - USA Number Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- ✅ Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- ✅ Alpine.js CDN (optional) -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Optional: Custom Font -->
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Figtree', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-indigo-100 flex items-center justify-center px-4">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
        <div class="mb-6 text-center">
            <h2 class="text-3xl font-extrabold text-gray-800">Welcome Back</h2>
            <p class="text-sm text-gray-500 mt-2">Login to your account</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-4 text-sm text-green-600 bg-green-100 border border-green-200 px-4 py-2 rounded">
                {{ session('status') }}
            </div>
        @endif
@if(session('token_conflict'))
    <div class="alert alert-danger">
        {{ session('error') }}
        <a href="{{ route('session.clear') }}">Click here to clear old session</a>
    </div>
@endif


        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <!-- Email -->
            <div class="relative">
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                <div class="flex items-center border rounded-lg shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500">
                    <span class="px-3 text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                             viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16v16H4z" stroke="none"/>
                            <path d="M4 4l8 8l8 -8" />
                        </svg>
                    </span>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full py-2 px-3 border-none focus:ring-0 text-sm placeholder-gray-400"
                           placeholder="you@example.com" />
                </div>
                @error('email')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="relative">
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                <div class="flex items-center border rounded-lg shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500">
                    <span class="px-3 text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                             viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 11c0-1.657 1.343-3 3-3s3 1.343 3 3" />
                            <path d="M4 11v6h16v-6a4 4 0 00-8 0v1" />
                        </svg>
                    </span>
                    <input id="password" type="password" name="password" required
                           class="w-full py-2 px-3 border-none focus:ring-0 text-sm placeholder-gray-400"
                           placeholder="••••••••" />
                </div>
                @error('password')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between mt-2">
                <label for="remember_me" class="flex items-center space-x-2">
                    <input id="remember_me" type="checkbox" name="remember"
                           class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="text-sm text-gray-600">Remember me</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        Forgot password?
                    </a>
                @endif
            </div>

            <!-- Sign In Button -->
            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Sign in
                </button>
            </div>
            
        </form>
@if (session('token_conflict'))
    <div class="bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-2 rounded mb-4 text-sm">
        You are already logged in on another device. Click below to clear the previous session.
    </div>

 <form method="POST" action="{{ route('session.clear') }}">


        @csrf
        <input type="hidden" name="email" value="{{ old('email') }}">
        <button type="submit"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 text-sm">
            🔓 Clear Previous Session
        </button>
    </form>
@endif


        <!-- Register Link -->
        <p class="text-center text-sm text-gray-500 mt-6">
            Don’t have an account?
            <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Sign up</a>
        </p>
    </div>

</body>
</html>

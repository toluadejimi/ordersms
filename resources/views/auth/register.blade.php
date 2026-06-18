<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register - USA Number Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            overflow-x: hidden;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }

        .password-toggle {
            top: 50%;
            transform: translateY(-50%);
            right: 1rem;
        }
    </style>
</head>

<body class="min-h-screen relative overflow-hidden">
    <div class="absolute w-[500px] h-[500px] bg-purple-500/20 rounded-full blur-3xl -top-64 -left-64"></div>
    <div class="absolute w-[500px] h-[500px] bg-indigo-500/20 rounded-full blur-3xl -bottom-64 -right-64"></div>

    <div class="relative z-10 flex items-center justify-center min-h-screen px-4">
        <div class="w-full max-w-md bg-white/90 backdrop-blur-xl rounded-2xl border border-gray-200 shadow-2xl p-8">
            <div class="mb-8 text-center space-y-2">
                <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-500 to-indigo-500 bg-clip-text text-transparent">
                    Get Started
                </h1>
                <p class="text-gray-500">Create your account in 30 seconds</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-5"
                x-data="{ showPassword: false, showPasswordConfirmation: false, password: '', password_confirmation: '' }">
                @csrf

                <!-- Full Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input id="name" name="name" type="text" required value="{{ old('name') }}"
                        class="w-full px-4 py-3 mt-1 bg-gray-100 border border-gray-300 rounded-lg text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/50 transition-all" />
                    @error('name')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input id="email" name="email" type="email" required value="{{ old('email') }}"
                        class="w-full px-4 py-3 mt-1 bg-gray-100 border border-gray-300 rounded-lg text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/50 transition-all" />
                    @error('email')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input id="phone" name="phone" type="tel" pattern="\d{11}" maxlength="11" minlength="11" required
                        value="{{ old('phone') }}"
                        class="w-full px-4 py-3 mt-1 bg-gray-100 border border-gray-300 rounded-lg text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/50 transition-all" />
                    @error('phone')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="relative">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input :type="showPassword ? 'text' : 'password'" x-model="password"
                        id="password" name="password" required
                        class="w-full px-4 py-3 pr-12 mt-1 bg-gray-100 border border-gray-300 rounded-lg text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/50 transition-all" />
                    <button type="button" @click="showPassword = !showPassword"
                        class="password-toggle absolute text-gray-500 hover:text-purple-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                    @error('password')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="relative">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input :type="showPasswordConfirmation ? 'text' : 'password'" x-model="password_confirmation"
                        id="password_confirmation" name="password_confirmation" required
                        class="w-full px-4 py-3 pr-12 mt-1 bg-gray-100 border border-gray-300 rounded-lg text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/50 transition-all" />
                    <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation"
                        class="password-toggle absolute text-gray-500 hover:text-purple-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                    @error('password_confirmation')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Referral Code -->
                <div>
                    <label for="ref" class="block text-sm font-medium text-gray-700">Referral Code (optional)</label>
                    <input id="ref" name="ref" type="text" value="{{ request('ref') }}"
                        class="w-full px-4 py-3 mt-1 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 focus:outline-none {{ request('ref') ? 'bg-emerald-50 border-emerald-400' : '' }}"
                        {{ request('ref') ? '' : '' }} />
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                        class="w-full bg-indigo-500 text-white py-3 rounded-lg text-lg font-semibold hover:bg-indigo-600 transition-all focus:outline-none">
                        Create Account
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center text-sm text-gray-500">
                <p>Already have an account? <a href="{{ route('login') }}"
                        class="text-indigo-500 hover:text-indigo-400 font-medium">Login here</a></p>
            </div>
        </div>
    </div>
</body>

</html>

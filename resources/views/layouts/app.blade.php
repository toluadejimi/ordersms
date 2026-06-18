<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen: false }" x-cloak class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireStyles

    <style>[x-cloak] { display: none !important; }</style>
</head>

<body class="bg-gray-100 text-gray-800 font-sans h-full">
@if (Auth::check() && !Auth::user()->hasVerifiedEmail() && request()->route()->getName() !== 'verification.notice')
    <script>window.location.href = "{{ route('verification.notice') }}";</script>
@endif
<div class="min-h-screen flex">
    <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-[#0F172A] text-white border-r border-gray-800 transform transition-transform duration-200 ease-in-out lg:hidden shadow-md"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" x-cloak>
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-700">
            <a href="{{ url('/') }}" class="text-2xl font-bold text-white">ORDERSSMS</a>
            <button @click="sidebarOpen = false" class="text-white text-xl">×</button>
        </div>
        <nav class="px-4 py-4 space-y-2 text-sm font-medium">
            <a href="/dashboard" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->is('dashboard') ? 'bg-slate-800 text-blue-400 font-semibold' : '' }}">🏠 <span class="ml-2">Dashboard</span></a>
            <a href="{{ route('transactions.index') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('transactions.index') ? 'bg-slate-800 text-blue-400 font-semibold' : '' }}">💼 <span class="ml-2">Transactions</span></a>
            <a href="/fund-wallet" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition">💳 <span class="ml-2">Fund Wallet</span></a>
                       <a href="/usa" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition">🔢 <span class="ml-2">USA Number </span></a>
                       
                       <a href="/sms-number" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition">🔢 <span class="ml-2">USA Number 2 </span></a>
            <a href="https://daviestore.com" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition">🛰 <span class="ml-2">Buy Logs</span></a>
            <a href="/verifications/server3" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition">📡 <span class="ml-2">All Countries Number 1</span></a>
            <a href="/verifications/server4" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition">📶 <span class="ml-2">All Countries Number 2</span></a>
            <a href="/virtual" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition">🌐 <span class="ml-2">All Countries Number 3</span></a>
            <a href="{{ route('verifications.sms_history') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('verifications.sms_history') ? 'bg-slate-800 text-blue-400 font-semibold' : '' }}">📜 <span class="ml-2">SMS History</span></a>
            <a href="/referrals" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->is('affiliate') ? 'bg-slate-800 text-blue-400 font-semibold' : '' }}">💰 <span class="ml-2">Affiliate Program</span></a>
            <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition">👤 <span class="ml-2">Profile</span></a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center w-full px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white transition">🔒 <span class="ml-2">Logout</span></button>
            </form>
        </nav>
    </aside>
    <aside class="hidden lg:block w-64 fixed inset-y-0 left-0 z-30 bg-[#0F172A] text-white shadow-md border-r border-gray-800">
        <div class="flex items-center justify-center px-6 py-5 border-b border-gray-700">
            <a href="{{ url('/') }}" class="text-2xl font-bold text-white">ORDERSSMS</a>
        </div>
        <nav class="px-4 py-6 space-y-2 text-sm font-medium">
            <a href="/dashboard" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->is('dashboard') ? 'bg-slate-800 text-blue-400 font-semibold' : '' }}">🏠 <span class="ml-2">Dashboard</span></a>
            <a href="{{ route('transactions.index') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('transactions.index') ? 'bg-slate-800 text-blue-400 font-semibold' : '' }}">💼 <span class="ml-2">Transactions</span></a>
            <a href="/fund-wallet" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition">💳 <span class="ml-2">Fund Wallet</span></a>
            <a href="/usa" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition">🔢 <span class="ml-2">USA Number 1</span></a>
               <a href="/sms-number" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition">🔢 <span class="ml-2">USA Number 2 </span></a>
            <a href="https://daviestore.com" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition">🛰 <span class="ml-2">Buy Logs</span></a>
            <a href="/verifications/server3" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition">📡 <span class="ml-2">All Countries Number 1</span></a>
            <a href="/verifications/server4" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition">📶 <span class="ml-2">All Countries Number 2</span></a>
            <a href="/virtual" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition">🌐 <span class="ml-2">All Countries Number 3</span></a>
            <a href="{{ route('verifications.sms_history') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('verifications.sms_history') ? 'bg-slate-800 text-blue-400 font-semibold' : '' }}">📜 <span class="ml-2">SMS History</span></a>
            <a href="/referrals" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->is('affiliate') ? 'bg-slate-800 text-blue-400 font-semibold' : '' }}">💰 <span class="ml-2">Affiliate Program</span></a>
            <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-slate-800 transition">👤 <span class="ml-2">Profile</span></a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center w-full px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white transition">🔒 <span class="ml-2">Logout</span></button>
            </form>
        </nav>
    </aside>
    <div class="flex flex-col min-h-screen w-full lg:ml-64">
        <header class="bg-[#0F172A] border-b border-gray-800 px-6 py-3 flex items-center justify-between text-white shadow">
            <div class="lg:hidden">
                <button @click="sidebarOpen = true" class="text-white hover:text-indigo-400 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
            <div class="flex items-center space-x-4 ml-auto">
                <div class="bg-indigo-600 text-white text-sm font-semibold px-3 py-1.5 rounded-full flex items-center shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v2m14 0v2a2 2 0 01-2 2H5a2 2 0 01-2-2V9m14 0h2a2 2 0 012 2v6a2 2 0 01-2 2h-2M7 16h.01M11 16h.01M15 16h.01" />
                    </svg>
                    ₦{{ number_format(auth()->user()->wallet, 2) }}
                </div>
                <a href="{{ route('profile.edit') }}">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=random&size=32" class="w-8 h-8 rounded-full border border-white hover:shadow transition duration-150">
                </a>
            </div>
        </header>
        <main class="w-full p-2 sm:p-6">
            @yield('content')
        </main>
    </div>
</div>
<footer class="bg-[#0F172A] text-white py-4 px-6 text-sm text-center w-full border-t border-gray-800">
    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
</footer>
@stack('scripts')
@yield('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<!-- Click to Chat Buttons (WhatsApp + Telegram) -->
<div class="fixed bottom-6 left-6 flex flex-col items-center space-y-3 z-50">
    <!-- WhatsApp Button -->
    <a href="https://chat.whatsapp.com/Kh4gUA91ylLGbLu9CritS6?mode=ems_copy_c" target="_blank" rel="noopener noreferrer"
       class="bg-green-500 hover:bg-green-600 text-white p-3 rounded-full shadow-lg transition duration-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="white" viewBox="0 0 24 24">
            <path d="M20.52 3.48A11.86 11.86 0 0012 0a11.94 11.94 0 00-10.38 6.13 11.81 11.81 0 00-1.1 4.94 11.94 11.94 0 001.65 6L0 24l6.25-2.05a11.86 11.86 0 0011.53-1.48 12 12 0 002.74-2.74 11.91 11.91 0 000-16.95zM12 22a9.94 9.94 0 01-5.09-1.4l-.36-.22-3.71 1.22 1.22-3.61-.23-.37A10 10 0 1112 22zm5.4-7.6c-.3-.15-1.76-.86-2.04-.96s-.48-.15-.69.15-.79.96-.96 1.16-.36.23-.66.08a8.3 8.3 0 01-2.44-1.5 9.3 9.3 0 01-1.72-2.12c-.18-.3 0-.46.13-.61.13-.14.3-.36.45-.54s.23-.3.3-.51a.59.59 0 000-.57c-.07-.15-.69-1.65-.96-2.27s-.51-.51-.69-.51-.38 0-.58 0a1.13 1.13 0 00-.82.38 3.42 3.42 0 00-1 2.54A6.5 6.5 0 009.07 14a11.6 11.6 0 005.37 1.5c.39 0 .78-.02 1.17-.05.36-.03 1.1-.43 1.26-.85s.16-1.03.08-1.12-.27-.19-.57-.34z"/>
        </svg>
    </a>

    <!-- Telegram Button -->
    <a href="https://t.me/DAVIESACCS24" target="_blank" rel="noopener noreferrer"
       class="bg-blue-500 hover:bg-blue-600 text-white p-3 rounded-full shadow-lg transition duration-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="white" viewBox="0 0 24 24">
            <path d="M9.042 16.555l-.389 3.875c.557 0 .798-.238 1.09-.524l2.614-2.497 5.416 3.943c.993.547 1.702.26 1.958-.921l3.546-16.652c.314-1.464-.518-2.032-1.486-1.677L1.99 10.062c-1.45.567-1.432 1.368-.252 1.736l5.667 1.77 13.127-8.27c.617-.375 1.177-.167.717.208l-10.05 9.11z"/>
        </svg>
    </a>
</div>

</body>
</html>

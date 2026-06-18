<!DOCTYPE html>
<html lang="en" x-data="{ sidebarOpen: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Panel - {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-100 font-sans text-gray-800">

<div class="flex min-h-screen">

    <!-- Sidebar (Desktop) -->
<!-- Sidebar (Desktop) -->
<!-- Desktop Sidebar -->
<aside class="w-64 bg-blue-900 shadow-md border-r hidden lg:flex flex-col fixed inset-y-0 left-0 z-30">
    <div class="p-4 border-b border-blue-800 flex justify-between items-center">
        <h2 class="text-xl font-bold text-white">Admin Panel</h2>
    </div>
    <nav class="px-4 py-4 space-y-2 text-sm">
        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded hover:bg-blue-800 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-800 text-white font-medium' : 'text-white' }}">🏠 Dashboard</a>
        <a href="/admin/users" class="block px-4 py-2 rounded hover:bg-blue-800 text-white">👥 Manage Users</a>
        <a href="/admin/transactions" class="block px-4 py-2 rounded hover:bg-blue-800 text-white">💳 Transactions</a>
        <a href="{{ route('admin.manual-fundings.index') }}" class="block px-4 py-2 rounded hover:bg-blue-800 text-white">💳 Manual Fundings</a>
        <a href="{{ route('admin.manual.account') }}" class="block px-4 py-2 rounded hover:bg-blue-800 {{ request()->routeIs('admin.manual.account') ? 'bg-blue-800 text-white font-medium' : 'text-white' }}">🏦 Manual Funding Account</a>
        <a href="/admin/verifications" class="block px-4 py-2 rounded hover:bg-blue-800 text-white">✅ Verifications</a>
        <a href="{{ route('admin.visitors.track') }}" class="block px-4 py-2 rounded hover:bg-blue-800 {{ request()->routeIs('admin.visitors.track') ? 'bg-blue-800 text-white font-medium' : 'text-white' }}">📊 Track Visitors</a>
        <a href="{{ route('admin.chat.inbox') }}"
   class="fixed bottom-5 right-5 z-50 w-14 h-14 flex items-center justify-center rounded-full bg-orange-600 hover:bg-orange-700 shadow-lg"
   title="Support Chats">
   <i class="fas fa-headset text-white text-xl"></i>
</a>

        <a href="/admin/settings" class="block px-4 py-2 rounded hover:bg-blue-800 text-white">⚙️ Settings</a>
        <a href="{{ route('admin.logs') }}" class="block px-4 py-2 rounded hover:bg-blue-800 {{ request()->routeIs('admin.logs') ? 'bg-blue-800 text-white font-medium' : 'text-white' }}">📋 Log Viewer</a>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="w-full text-left px-4 py-2 text-red-500 hover:bg-red-100 rounded">🔒 Logout</button>
        </form>
    </nav>
</aside>

<!-- Mobile Sidebar -->
<aside class="lg:hidden fixed inset-y-0 left-0 w-64 bg-blue-900 shadow-md z-40 transform transition-transform duration-300 ease-in-out"
       :class="{ '-translate-x-full': !sidebarOpen }" x-cloak>
    <div class="p-4 border-b border-blue-800 flex justify-between items-center">
        <h2 class="text-xl font-bold text-white">Admin Panel</h2>
        <button class="text-white" @click="sidebarOpen = false">✖</button>
    </div>
    <nav class="px-4 py-4 space-y-2 text-sm">
        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded hover:bg-blue-800 text-white">🏠 Dashboard</a>
        <a href="/admin/users" class="block px-4 py-2 rounded hover:bg-blue-800 text-white">👥 Manage Users</a>
        <a href="/admin/transactions" class="block px-4 py-2 rounded hover:bg-blue-800 text-white">💳 Transactions</a>
        <a href="{{ route('admin.manual-fundings.index') }}" class="block px-4 py-2 rounded hover:bg-blue-800 text-white">💳 Manual Fundings</a>
        <a href="{{ route('admin.manual.account') }}" class="block px-4 py-2 rounded hover:bg-blue-800 text-white">🏦 Manual Funding Account</a>
        <a href="/admin/verifications" class="block px-4 py-2 rounded hover:bg-blue-800 text-white">✅ Verifications</a>
        <a href="{{ route('admin.visitors.track') }}" class="block px-4 py-2 rounded hover:bg-blue-800 text-white">📊 Track Visitors</a>
        <a href="/admin/settings" class="block px-4 py-2 rounded hover:bg-blue-800 text-white">⚙️ Settings</a>
        <a href="{{ route('admin.logs') }}" class="block px-4 py-2 rounded hover:bg-blue-800 {{ request()->routeIs('admin.logs') ? 'bg-blue-800 text-white font-medium' : 'text-white' }}">📋 Log Viewer</a>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="w-full text-left px-4 py-2 text-red-500 hover:bg-red-100 rounded">🔒 Logout</button>
        </form>
    </nav>
</aside>



    <!-- Main Content -->
    <div class="flex-1 lg:ml-64 flex flex-col overflow-hidden">
        <!-- Topbar -->
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-4 border-b">
            <button class="lg:hidden text-gray-600" @click="sidebarOpen = true">☰</button>
            <div class="text-sm text-gray-500">
                Logged in as <strong>{{ auth('admin')->user()->name ?? 'Admin' }}</strong>
            </div>
        </header>

          <div class="flex-1 flex flex-col overflow-y-auto min-h-screen"> <!-- ✅ Stretch container -->
    
    <main class="w-full flex-1  px-1"> <!-- ✅ Full-width main content -->
        @yield('content')
    </main>
   
</div>

</body>
</html>

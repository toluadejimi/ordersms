@extends('layouts.admin')

@section('content')
<div class="">
    <!-- Dashboard Header -->
<div class="bg-gradient-to-r from-indigo-600 to-blue-500 rounded-xl p-6 shadow-xl text-white">
    <h1 class="text-3xl font-bold mb-2">Dashboard Overview</h1>
    <p class="opacity-90">Key metrics and recent verification activities</p>

    <!-- Unified Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mt-8">
        <!-- Total Users -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 hover:scale-[1.02] transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Total Users</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($totalUsers) }}</p>
                </div>
                <div class="p-3 bg-white/20 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Verifications -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 hover:scale-[1.02] transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Total Verifications</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($totalVerifications) }}</p>
                </div>
                <div class="p-3 bg-white/20 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Money In -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 hover:scale-[1.02] transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Total Money In</p>
                    <p class="text-2xl font-bold mt-1 text-emerald-400">₦{{ number_format($totalMoneyIn, 2) }}</p>
                </div>
                <div class="p-3 bg-white/20 rounded-full">
                    <svg class="w-6 h-6 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c1.1 0 2 .9 2 2s-.9 2-2 2m0 0c-1.1 0-2 .9-2 2s.9 2 2 2m0-8v8m0-8H8m4 0h4"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Money Out -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 hover:scale-[1.02] transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Total Money Out</p>
                    <p class="text-2xl font-bold mt-1 text-red-400">₦{{ number_format($totalMoneyOut, 2) }}</p>
                </div>
                <div class="p-3 bg-white/20 rounded-full">
                    <svg class="w-6 h-6 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 16c-1.1 0-2-.9-2-2s.9-2 2-2m0 0c1.1 0 2-.9 2-2s-.9-2-2-2m0 8V8m0 8H8m4 0h4"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Wallet Balance -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 hover:scale-[1.02] transition-all xl:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Total Wallet Balance</p>
                    <p class="text-2xl font-bold mt-1">₦{{ number_format($totalWalletBalance, 2) }}</p>
                </div>
                <div class="p-3 bg-white/20 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>


  
<br>
    <!-- Verifications Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-semibold">Recent Verifications</h3>
            <div class="flex items-center space-x-4">
                <button class="flex items-center text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filters
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pricing</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($verifications as $verification)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $verification->user->email ?? 'N/A' }}</div>
                            <div class="text-sm text-gray-500">{{ $verification->country_id ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ strtoupper($verification->name ?? $verification->service) }}</div>
                            <div class="text-sm text-gray-500">{{ $verification->server }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm">{{ $verification->number ?? '-' }}</div>
                            <div class="text-sm text-gray-500">{{ $verification->code ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm">${{ $verification->price ?? '0.00' }}</div>
                            <div class="text-sm text-gray-500">₦{{ number_format($verification->naira_amount, 2) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'success' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'failed' => 'bg-red-100 text-red-800'
                                ];
                            @endphp
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[strtolower($verification->status)] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($verification->status ?? 'pending') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm">{{ $verification->created_at->format('d M Y') }}</div>
                            <div class="text-sm text-gray-500">{{ $verification->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                                    <div class="py-1">
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Details</a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export</a>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center">
                            <div class="text-gray-400">
                                <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No verifications found</h3>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

      
    </div>
</div>
@endsection
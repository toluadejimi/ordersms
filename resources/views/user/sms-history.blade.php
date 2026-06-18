@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 ">
    <div class="w-full">
        <div class="w-full bg-gradient-to-br from-white to-blue-50 shadow-2xl border border-gray-100 overflow-hidden rounded-none sm:rounded-2xl">
            <!-- Header Section -->
            <div class="px-6 py-5 bg-white border-b border-gray-100">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-2xl font-bold text-gray-900">SMS Verification History</h1>
                        <p class="mt-1 text-sm text-gray-500">Track all your SMS verification activities</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-sm">
                            <span class="text-sm font-medium">Total:</span>
                            <span class="font-bold ml-1">{{ number_format($totalVerifications) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-50/80 backdrop-blur-sm">
                        <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="pl-6 pr-3 py-4 text-left">Service</th>
                            <th class="px-3 py-4 text-left">Number</th>
                            <th class="px-3 py-4 text-left">Country</th>
                            <th class="px-3 py-4 text-left">Server</th>
                            <th class="px-3 py-4 text-right">Price</th>
                            <th class="px-3 py-4 text-center">Status</th>
                            <th class="px-3 py-4 text-right">Code</th>
                            <th class="px-3 py-4 text-left">Date</th>
                            <th class="pr-6 pl-3 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($allVerifications as $verification)
                            @php
                                $serverConfig = [
                                    'server1' => ['name' => 'S1', 'color' => 'bg-purple-500/10 text-purple-700'],
                                    'server2' => ['name' => 'S2', 'color' => 'bg-sky-500/10 text-sky-700'],
                                    'server3' => ['name' => 'S3', 'color' => 'bg-emerald-500/10 text-emerald-700'],
                                    'server4' => ['name' => 'S4', 'color' => 'bg-indigo-500/10 text-indigo-700'],
                                ];
                                $statusConfig = [
                                    'completed' => ['color' => 'bg-green-100 text-green-700', 'icon' => '✓'],
                                    'received' => ['color' => 'bg-green-100 text-green-700', 'icon' => '✓'],
                                    'active' => ['color' => 'bg-amber-100 text-amber-700', 'icon' => '⟳'],
                                    'waiting' => ['color' => 'bg-amber-100 text-amber-700', 'icon' => '…'],
                                    'reserved' => ['color' => 'bg-blue-100 text-blue-700', 'icon' => '⧗'],
                                    'cancelled' => ['color' => 'bg-red-100 text-red-700', 'icon' => '✕'],
                                ];
                                $serverKey = strtolower($verification->server);
                                $server = $serverConfig[$serverKey] ?? ['name' => $verification->server, 'color' => 'bg-gray-100 text-gray-700'];
                                $statusKey = strtolower($verification->status);
                                $status = $statusConfig[$statusKey] ?? ['color' => 'bg-gray-100 text-gray-700', 'icon' => '—'];
                            @endphp

                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="pl-6 pr-3 py-4 font-medium text-gray-900">
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 rounded-full bg-blue-500 mr-3"></div>
                                        {{ $verification->service_name ?? $verification->service }}
                                    </div>
                                </td>
                                <td class="px-3 py-4 font-mono text-gray-900">{{ $verification->number ?? '---' }}</td>
                                <td class="px-3 py-4 flex items-center">
                                    <span class="mr-2 text-lg">🇺🇸</span> {{ $verification->country_name ?? '---' }}
                                </td>
                                <td class="px-3 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium {{ $server['color'] }}">
                                        {{ $server['name'] }}
                                    </span>
                                </td>
                               <td class="px-3 py-4 text-right font-medium text-gray-900">
    <div class="flex flex-col">
        @if(in_array($serverKey, ['1', 'server2']))
            <span>₦{{ number_format($verification->price, 0) }}</span
        @elseif(in_array($serverKey, ['3', '4', 'server6']))
            <span>₦{{ number_format($verification->naira_amount, 2) }}</span>
        @else
            <span>₦{{ number_format($verification->price, 0) }}</span> {{-- fallback --}}
        @endif
        <span class="text-xs text-gray-400">+ tax</span>
    </div>
</td>

                                <td class="px-3 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $status['color'] }}">
                                        <span class="mr-1">{{ $status['icon'] }}</span>
                                        {{ ucfirst($verification->status) }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 text-right font-mono text-gray-900">{{ $verification->code ?? '---' }}</td>
                                <td class="px-3 py-4 text-gray-500">
                                    <div class="text-sm">{{ $verification->created_at->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-400">{{ $verification->created_at->format('H:i') }}</div>
                                </td>
                                <td class="pr-6 pl-3 py-4 text-right">
                                    @if(in_array($statusKey, ['active', 'waiting', 'reserved']))
                                        <div x-data="{ submitting: false }">
                                            <form method="GET" 
                                                action="{{ route('verifications.cancel', $verification->id) }}" 
                                                @submit.prevent="if (!submitting) { submitting = true; $el.submit(); }">
                                                <button type="submit"
                                                        x-bind:disabled="submitting"
                                                        class="inline-flex items-center text-red-600 hover:text-red-800 transition-colors text-sm font-medium"
                                                        x-bind:class="submitting ? 'opacity-60 cursor-not-allowed' : ''">
                                                    <svg x-show="!submitting" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                    <span x-show="!submitting">Cancel</span>
                                                    <span x-show="submitting" class="flex items-center">
                                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                                        </svg>
                                                        Processing...
                                                    </span>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-4">
                                        <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <div class="text-gray-500">No verification records found</div>
                                        <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            Try a new verification →
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($allVerifications->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-white">
                    {{ $allVerifications->onEachSide(1)->links('pagination::tailwind') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

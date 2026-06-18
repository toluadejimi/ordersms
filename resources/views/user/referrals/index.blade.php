@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Affiliate Program</h1>
        <p class="text-gray-500">Earn rewards by sharing your unique referral link</p>
    </div>

    <!-- Referral Link Card -->
    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl p-6 mb-8 shadow-lg border border-gray-100">
        <div class="flex flex-col space-y-4">
            <div class="flex items-center space-x-2">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
                <span class="font-semibold text-gray-700">Your Unique Referral Link</span>
            </div>
            <div class="flex gap-3">
                <input 
                    id="refLink" 
                    value="{{ url('/register?ref=' . $user->referral_code) }}" 
                    readonly
                    class="w-full px-4 py-3 bg-white border-2 border-purple-100 rounded-lg focus:outline-none focus:border-purple-300 transition-all"
                >
                <button 
                    onclick="copyLink()"
                    class="flex items-center px-6 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold rounded-lg hover:shadow-lg hover:scale-[1.02] transition-all"
                >
                    <span>Copy</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </button>
            </div>
            <p id="copyFeedback" class="text-sm text-green-600 opacity-0 transition-opacity"></p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Referrals</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $referrals->count() }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="h-1 bg-gray-100 rounded-full">
                    <div class="h-1 bg-purple-600 rounded-full" style="width: {{ min(($referrals->count()/50)*100, 100) }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Next bonus at 50 referrals</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Earnings</p>
                    <p class="text-3xl font-bold text-gray-900">₦{{ number_format($user->referral_earnings, 2) }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                @if($user->referral_earnings >= 500)
                <form method="POST" action="{{ route('referrals.withdraw') }}">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Withdraw referral earnings to your wallet?')"
                            class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white py-3 rounded-lg font-semibold hover:shadow-lg transition-all">
                        Withdraw ₦{{ number_format($user->referral_earnings, 2) }}
                    </button>
                </form>
                @else
                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-100">
                    <p class="text-sm text-yellow-700">
                        <span class="font-semibold">₦{{ number_format(500 - $user->referral_earnings, 2) }}</span> 
                        needed to withdraw
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Referral List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Referred Users</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-500">User</th>
                        
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-500">Status</th>
                        
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-500">Join Date</th>
                      
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($referrals as $ref)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                        <span class="text-purple-600 font-medium">{{ substr($ref->name, 0, 1) }}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $ref->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $ref->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Verified
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $ref->created_at?->format('M d, Y') ?? 'N/A' }}
                        </td>
  


                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="text-gray-400 mb-4">
                                <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <p class="text-gray-500">No referrals yet. Start sharing your link!</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function copyLink() {
    const link = document.getElementById('refLink');
    const feedback = document.getElementById('copyFeedback');
    
    navigator.clipboard.writeText(link.value).then(() => {
        feedback.textContent = 'Link copied to clipboard!';
        feedback.classList.add('opacity-100');
        setTimeout(() => feedback.classList.remove('opacity-100'), 2000);
    }).catch(err => {
        feedback.textContent = 'Failed to copy!';
        feedback.classList.add('opacity-100');
    });
}
</script>
@endsection
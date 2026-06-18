@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">DASHBOARD</h1>

    {{-- Flash messages --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show"
             class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded mb-6 shadow flex justify-between items-center">
            <span>{{ session('success') }}</span>
            <button @click="show = false" class="text-green-800 font-bold px-2">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-show="show"
             class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded mb-6 shadow flex justify-between items-center">
            <span>{{ session('error') }}</span>
            <button @click="show = false" class="text-red-800 font-bold px-2">&times;</button>
        </div>
    @endif
@if(session()->has('impersonate_admin_id'))
    <form method="POST" action="{{ route('admin.users.leave') }}" class="mt-4">
        @csrf
        <button type="submit"
                class="inline-flex items-center gap-2 bg-red-100 hover:bg-red-200 text-red-700 font-medium px-4 py-2 rounded-lg transition shadow-sm border border-red-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            <span>Return to Admin</span>
        </button>
    </form>
    <br>
<br>
@endif


    @if($virtualAccountEnabled)
<div class=" mx-auto mb-8" x-data="{ showPhoneModal: false }">
    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl shadow-xl p-6 text-white overflow-hidden relative">
        <div class="absolute -right-6 -top-6 bg-white/10 w-24 h-24 rounded-full"></div>
        <div class="absolute -right-14 -top-14 bg-white/5 w-36 h-36 rounded-full"></div>

        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">🏦 Virtual Account</h2>
                <svg class="w-8 h-8 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>

            @if(auth()->user()->virtual_account_number)
                <div class="space-y-4">
                    <div>
                        <p class="text-sm opacity-90">Account Number</p>
                        <p class="text-2xl font-mono font-bold">{{ auth()->user()->virtual_account_number }}</p>
                    </div>
                    <div class="flex space-x-8">
                        <div>
                            <p class="text-sm opacity-90">Bank Name</p>
                            <p class="font-medium">{{ auth()->user()->virtual_account_bank ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm opacity-90">Account Name</p>
                            <p class="font-medium">{{ auth()->user()->virtual_account_name ?? auth()->user()->name }}</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    @if(empty(auth()->user()->phone))
                        <button @click="showPhoneModal = true"
                                class="inline-flex items-center space-x-2 bg-white/20 hover:bg-white/30 px-6 py-2 rounded-lg transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>Generate Account</span>
                        </button>
                    @else
                        <form method="POST" action="{{ route('virtual.account.generate') }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center space-x-2 bg-white/20 hover:bg-white/30 px-6 py-2 rounded-lg transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <span>Generate Account</span>
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Phone Modal -->
    <div x-show="showPhoneModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white w-full max-w-md p-6 rounded-2xl shadow-xl relative">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">📱 Enter Your Phone Number</h3>
            <form method="POST" action="{{ route('virtual.account.phone.submit') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input
                        type="tel"
                        name="phone"
                        required
                        pattern="[0-9]{11}"
                        maxlength="11"
                        minlength="11"
                        inputmode="numeric"
                        placeholder="e.g. 08123456789"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none text-lg"
                    >
                    @error('phone')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showPhoneModal = false"
                            class="px-4 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
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
    <div class="mx-auto grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center space-x-4">
                <div class="bg-green-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Available Balance</p>
                    <p class="text-2xl font-bold text-gray-900">₦{{ number_format($balance, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center space-x-4">
                <div class="bg-blue-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Verifications</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalVerified }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center space-x-4">
                <div class="bg-purple-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Spent</p>
                    <p class="text-2xl font-bold text-gray-900">₦{{ number_format(auth()->user()->verifications()->sum('price'), 2) }}</p>
                </div>
            </div>
        </div>
    </div>


{{-- Quick Actions --}}
<div class="bg-white rounded-2xl shadow-xl p-6 mb-8 border border-gray-100">
    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
        <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        Instant Server Access
    </h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!--<a href="" class="group relative p-6 bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl hover:shadow-lg transition-all duration-300">-->
        <!--    <div class="absolute top-3 right-3 bg-indigo-600 text-white px-2 py-1 rounded-full text-xs font-medium">USA</div>-->
        <!--    <div class="flex flex-col items-center text-center">-->
        <!--        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-indigo-200 transition">-->
        <!--            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
        <!--                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4m0 0v4m0-4h4m-4 0H3m12-8v4m0 0v4m0-4h4m-4 0h-4M3 7v4m0 0v4m0-4h4m-4 0H3"/>-->
        <!--            </svg>-->
        <!--        </div>-->
        <!--        <h3 class="font-semibold text-gray-900 mb-1">USA Server 1</h3>-->
        <!--        <p class="text-sm text-gray-600">Premium Numbers</p>-->
        <!--    </div>-->
        <!--</a>-->

        <!--<a href="/server2" class="group relative p-6 bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl hover:shadow-lg transition-all duration-300">-->
        <!--    <div class="absolute top-3 right-3 bg-blue-600 text-white px-2 py-1 rounded-full text-xs font-medium">USA</div>-->
        <!--    <div class="flex flex-col items-center text-center">-->
        <!--        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-blue-200 transition">-->
        <!--            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
        <!--                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>-->
        <!--            </svg>-->
        <!--        </div>-->
        <!--        <h3 class="font-semibold text-gray-900 mb-1">USA Server 2</h3>-->
        <!--        <p class="text-sm text-gray-600">Backup System</p>-->
        <!--    </div>-->
        <!--</a>-->

        <a href="/verifications/server3" class="group relative p-6 bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl hover:shadow-lg transition-all duration-300">
            <div class="absolute top-3 right-3 bg-green-600 text-white px-2 py-1 rounded-full text-xs font-medium">Global</div>
            <div class="flex flex-col items-center text-center">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-green-200 transition">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">All Countries Server 1</h3>
                <p class="text-sm text-gray-600">Worldwide Coverage</p>
            </div>
        </a>

        <a href="/verifications/server4" class="group relative p-6 bg-gradient-to-br from-purple-50 to-violet-50 rounded-xl hover:shadow-lg transition-all duration-300">
            <div class="absolute top-3 right-3 bg-purple-600 text-white px-2 py-1 rounded-full text-xs font-medium">Global</div>
            <div class="flex flex-col items-center text-center">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-purple-200 transition">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">All Countries Server 2</h3>
                <p class="text-sm text-gray-600">Multi-country Support</p>
            </div>
        </a>
    </div>
</div>

    {{-- Recent Verifications --}}
    <div class="bg-white shadow rounded-lg mt-10">
        <div class="px-6 pt-6 border-b">
            <h3 class="text-md font-semibold text-indigo-700">Recent Verifications</h3>
        </div>

        @if ($verifications->count())
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700">
                <thead class="bg-indigo-50 text-gray-600 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-4">Service</th>
                        <th class="px-6 py-4">Number</th>
                        <th class="px-6 py-4">Code</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach ($verifications as $verification)
                        @if (strtolower($verification->status) === 'reserved')
                            @continue
                        @endif
                        <tr class="hover:bg-indigo-50 transition duration-150">
                            <td class="px-6 py-4">{{ strtoupper($verification->name ?? $verification->service) }}</td>
                            <td class="px-6 py-4">{{ $verification->number ?? '—' }}</td>
                            <td class="px-6 py-4">{{ $verification->code ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ in_array($verification->status, ['done', 'completed', 'received']) ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ ucfirst($verification->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $verification->created_at->format('d M Y, h:i A') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

    </div>
    @else
    <div class="text-center text-gray-500 text-sm py-6">
        <p>No recent verifications found.</p>
    </div>
    @endif
</div>


{{-- Recent Transactions --}}
<div class="bg-white shadow rounded-lg mt-8">
    <div class="px-6 pt-6 border-b">
        <h3 class="text-md font-semibold text-indigo-700">Recent Transactions</h3>
    </div>

    @if ($transactions->count())
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-blue-50 text-gray-600 uppercase text-xs font-semibold">
                <tr>
                    <th class="px-6 py-4">Type</th>
                    <th class="px-6 py-4">Method</th>
                    <th class="px-6 py-4">Amount</th>
                    <th class="px-6 py-4">Date</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach ($transactions as $txn)
                <tr class="hover:bg-blue-50 transition duration-150">
                    <td class="px-6 py-4 font-medium">
                        <span class="inline-flex items-center gap-2">
                            @if($txn->type === 'credit')
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" /></svg>
                            @else
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" /></svg>
                            @endif
                            {{ ucfirst($txn->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 capitalize text-blue-600">{{ $txn->method ?? 'N/A' }}</td>
                    <td class="px-6 py-4 font-semibold {{ $txn->type === 'debit' ? 'text-red-600' : 'text-green-600' }}">
                        ₦{{ number_format($txn->amount, 2) }}
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        {{ $txn->created_at->format('d M Y, h:i A') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
        <div class="text-center text-gray-500 text-sm mt-12">
            <p>No transactions found.</p>
        </div>
    @endif
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

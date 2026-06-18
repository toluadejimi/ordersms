@extends('layouts.app')

@section('content')
@php
    use App\Models\Setting;
    use App\Models\ManualFundingAccount;

    $account = ManualFundingAccount::latest()->first();
    $paystackEnabled = Setting::get('paystack_enabled') === '1';
    $flutterwaveEnabled = Setting::get('flutterwave_enabled') === '1';
    $sprintpayEnabled = Setting::get('sprintpay_enabled') === '1';
    $manualEnabled = Setting::get('manual_payment_enabled') === '1';
    $virtualAccountEnabled = Setting::get('virtual_account_enabled') === '1';

    $defaultTab = $paystackEnabled ? 'paystack' : ($flutterwaveEnabled ? 'flutterwave' : ($sprintpayEnabled ? 'sprintpay' : 'manual'));
@endphp

<div class="min-h-screen bg-gradient-to-b from-gray-50 to-white py-12" x-data="{ activeTab: '{{ $defaultTab }}' }">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-extrabold text-gray-900 mb-3">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600">Fund Your Wallet</span>
            </h1>
            <p class="text-lg text-gray-600">Secure payment methods to top up your account balance</p>
        </div>

        <!-- Virtual Account Section -->
       
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
      

        <!-- Status Messages -->
        <div class="space-y-4 mb-8">
            @if (session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-400 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-emerald-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if (session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Payment Tabs -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <!-- Tab Navigation -->
            <nav class="border-b border-gray-200">
                <div class="flex space-x-8 px-6" aria-label="Tabs">
                    @if($paystackEnabled)
                    <button @click="activeTab = 'paystack'" :class="activeTab === 'paystack' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        Paystack
                    </button>
                    @endif

                    @if($flutterwaveEnabled)
                    <button @click="activeTab = 'flutterwave'" :class="activeTab === 'flutterwave' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        Flutterwave
                    </button>
                    @endif

                    @if($sprintpayEnabled)
                    <button @click="activeTab = 'sprintpay'" :class="activeTab === 'sprintpay' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        SprintPay
                    </button>
                    @endif

                    @if($manualEnabled)
                    <button @click="activeTab = 'manual'" :class="activeTab === 'manual' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Bank Transfer
                    </button>
                    @endif
                </div>
            </nav>

            <!-- Tab Content -->
            <div class="p-8">
                <!-- Paystack Content -->
                @if($paystackEnabled)
                <div x-show="activeTab === 'paystack'" x-cloak class="space-y-6">
                    <form method="POST" action="{{ route('funding.paystack.redirect') }}">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Amount (NGN)</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500">₦</span>
                                    </div>
                                    <input type="number" name="amount" min="100" required class="block w-full pl-10 pr-12 py-3 border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="5000">
                                </div>
                            </div>
                            <button type="submit" class="w-full flex justify-center items-center px-6 py-3.5 border border-transparent rounded-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors duration-200">
                                Continue to Paystack
                            </button>
                        </div>
                    </form>
                </div>
                @endif

                <!-- Flutterwave Content -->
                @if($flutterwaveEnabled)
                <div x-show="activeTab === 'flutterwave'" x-cloak class="space-y-6">
                    <form method="POST" action="{{ route('funding.flutterwave.redirect') }}">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Amount (NGN)</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500">₦</span>
                                    </div>
                                    <input type="number" name="amount" min="100" required class="block w-full pl-10 pr-12 py-3 border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="5000">
                                </div>
                            </div>
                            <button type="submit" class="w-full flex justify-center items-center px-6 py-3.5 border border-transparent rounded-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors duration-200">
                                Continue to Flutterwave
                            </button>
                        </div>
                    </form>
                </div>
                @endif

                <!-- SprintPay Content -->
                @if($sprintpayEnabled)
                <div x-show="activeTab === 'sprintpay'" x-cloak class="space-y-6"
                     x-data="{
                        amount: '',
                        loading: false,
                        verifying: false,
                        polling: false,
                        pollTimer: null,
                        error: '',
                        success: '',
                        account: null,
                        async generateAccount() {
                            this.error = '';
                            this.success = '';
                            this.loading = true;
                            this.stopPolling();
                            try {
                                const res = await fetch('{{ route('funding.sprintpay.account') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                    },
                                    body: JSON.stringify({ amount: this.amount }),
                                });
                                const data = await res.json();
                                if (!res.ok || !data.status) {
                                    this.error = data.message || 'Failed to generate account.';
                                    this.account = null;
                                    return;
                                }
                                this.account = data;
                                this.startPolling();
                            } catch (e) {
                                this.error = 'Network error. Please try again.';
                            } finally {
                                this.loading = false;
                            }
                        },
                        async verifyPayment() {
                            if (!this.account?.ref) return;
                            this.error = '';
                            this.verifying = true;
                            try {
                                const res = await fetch('{{ route('funding.sprintpay.verify') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                    },
                                    body: JSON.stringify({ ref: this.account.ref }),
                                });
                                const data = await res.json();
                                if (data.credited) {
                                    this.stopPolling();
                                    this.success = 'Payment confirmed! Your wallet has been credited.';
                                    setTimeout(() => window.location.href = '{{ route('dashboard') }}', 2000);
                                    return;
                                }
                                this.error = data.message || 'Payment not received yet.';
                            } catch (e) {
                                this.error = 'Network error. Please try again.';
                            } finally {
                                this.verifying = false;
                            }
                        },
                        startPolling() {
                            this.stopPolling();
                            this.polling = true;
                            this.pollTimer = setInterval(() => this.verifyPayment(), 15000);
                        },
                        stopPolling() {
                            this.polling = false;
                            if (this.pollTimer) {
                                clearInterval(this.pollTimer);
                                this.pollTimer = null;
                            }
                        },
                        copyText(text) {
                            navigator.clipboard.writeText(text);
                        }
                     }"
                     x-init="return () => stopPolling()">
                    <p class="text-sm text-gray-600">Enter an amount and continue to SprintPay to complete your payment. Your wallet is credited automatically after payment.</p>

                    <div x-show="error" class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                        <p class="text-sm text-red-700" x-text="error"></p>
                    </div>
                    <div x-show="success" class="bg-emerald-50 border-l-4 border-emerald-400 p-4 rounded-lg">
                        <p class="text-sm text-emerald-700" x-text="success"></p>
                    </div>

                    <div class="space-y-4" x-show="!account">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount (NGN)</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">₦</span>
                                </div>
                                <input type="number" x-model="amount" min="100" required
                                    class="block w-full pl-10 pr-12 py-3 border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="5000">
                            </div>
                        </div>
                        <form method="POST" action="{{ route('funding.sprintpay.redirect') }}">
                            @csrf
                            <input type="hidden" name="amount" x-bind:value="amount">
                            <button type="submit" :disabled="!amount || amount < 100"
                                class="w-full flex justify-center items-center px-6 py-3.5 rounded-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                Continue
                            </button>
                        </form>
                        <div class="border-t border-gray-200 pt-4 text-center">
                            <button type="button" @click="generateAccount()" :disabled="loading || !amount || amount < 100"
                                class="text-sm text-indigo-600 hover:text-indigo-800 disabled:text-gray-400 disabled:cursor-not-allowed">
                                <span x-show="!loading">Generate Account</span>
                                <span x-show="loading">Generating...</span>
                            </button>
                        </div>
                    </div>

                    <div x-show="account" class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900">Transfer to this account</h3>
                        <p class="text-sm text-amber-700 font-medium">Pay the exact amount below. Fees may be included in the total.</p>
                        <div class="grid sm:grid-cols-2 gap-4 text-gray-800">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Bank</p>
                                <p class="font-medium" x-text="account?.bank_name"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Account Name</p>
                                <p class="font-medium" x-text="account?.account_name"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Account Number</p>
                                <div class="flex items-center gap-2">
                                    <p class="text-xl font-mono font-bold" x-text="account?.account_no"></p>
                                    <button type="button" @click="copyText(account.account_no)" class="text-indigo-600 hover:text-indigo-700 text-sm">Copy</button>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Amount to Pay</p>
                                <div class="flex items-center gap-2">
                                    <p class="text-xl font-bold text-indigo-700">₦<span x-text="Number(account?.amount_to_pay).toLocaleString()"></span></p>
                                    <button type="button" @click="copyText(String(account.amount_to_pay))" class="text-indigo-600 hover:text-indigo-700 text-sm">Copy</button>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3 pt-2">
                            <button type="button" @click="verifyPayment()" :disabled="verifying"
                                class="flex-1 px-6 py-3 rounded-lg font-medium text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 transition-colors">
                                <span x-show="!verifying">I've Paid — Verify</span>
                                <span x-show="verifying">Checking...</span>
                            </button>
                            <button type="button" @click="account = null; stopPolling(); error = ''; success = '';"
                                class="px-6 py-3 rounded-lg font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors">
                                New Amount
                            </button>
                        </div>
                        <p x-show="polling" class="text-xs text-gray-500 text-center">Auto-checking payment status every 15 seconds...</p>
                    </div>
                </div>
                @endif

                <!-- Manual Transfer Content -->
                @if($manualEnabled && $account)
                <div x-show="activeTab === 'manual'" x-cloak class="space-y-8">
                    <div class="bg-indigo-50 border-l-4 border-indigo-400 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Bank Transfer Instructions</h3>
                        <div class="space-y-4 text-gray-700">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span><strong>Bank:</strong> {{ $account->bank_name }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span><strong>Account Name:</strong> {{ $account->account_name }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                                <span><strong>Account Number:</strong> {{ $account->account_number }}</span>
                                <button onclick="navigator.clipboard.writeText('{{ $account->account_number }}')" class="text-indigo-600 hover:text-indigo-700 text-sm flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                    Copy
                                </button>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('funding.manual.submit') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Transfer Amount (NGN)</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500">₦</span>
                                    </div>
                                    <input type="number" name="amount" min="100" required class="block w-full pl-10 pr-12 py-3 border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter transferred amount">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Proof</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-gray-400 transition-colors">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                <span>Upload a file</span>
                                                <input name="proof" type="file" class="sr-only" accept="image/*">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG up to 2MB</p>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="w-full flex justify-center items-center px-6 py-3.5 border border-transparent rounded-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors duration-200">
                                Submit Payment Proof
                            </button>
                        </div>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
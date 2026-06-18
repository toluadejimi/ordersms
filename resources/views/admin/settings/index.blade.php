@extends('layouts.admin')

@section('content')
<div class="mx-auto space-y-6 max-w-7xl">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">System Settings</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage API keys, pricing, and payment gateway configuration</p>
        </div>
        <button type="submit" form="settingsForm"
            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold text-sm transition shadow">
            Save All Settings
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-300 text-green-800 px-4 py-3 rounded-lg shadow-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <form id="settingsForm" method="POST" action="{{ route('admin.settings.update') }}">
        @csrf

        {{-- ═══════════════════════════════════════════ --}}
        {{-- SECTION 1: PAYMENT GATEWAYS               --}}
        {{-- ═══════════════════════════════════════════ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-slate-700 to-slate-600 px-5 py-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <h2 class="text-sm font-bold text-white">Payment Gateways</h2>
            </div>
            <div class="p-5 grid md:grid-cols-3 gap-6">

                {{-- Toggles --}}
                <div class="md:col-span-3">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Enable / Disable</h3>
                    <div class="flex flex-wrap gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="paystack_enabled" value="1"
                                class="form-checkbox h-4 w-4 text-indigo-600 rounded"
                                {{ old('paystack_enabled', $paystackEnabled) ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-gray-700">Paystack</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="flutterwave_enabled" value="1"
                                class="form-checkbox h-4 w-4 text-yellow-500 rounded"
                                {{ old('flutterwave_enabled', $flutterwaveEnabled) ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-gray-700">Flutterwave</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="sprintpay_enabled" value="1"
                                class="form-checkbox h-4 w-4 text-blue-500 rounded"
                                {{ old('sprintpay_enabled', $sprintpayEnabled) ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-gray-700">SprintPay</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="virtual_account_enabled" value="1"
                                class="form-checkbox h-4 w-4 text-green-600 rounded"
                                {{ old('virtual_account_enabled', $virtualAccountEnabled) ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-gray-700">Virtual Account</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="manual_payment_enabled" value="1"
                                class="form-checkbox h-4 w-4 text-gray-600 rounded"
                                {{ old('manual_payment_enabled', $manualPaymentEnabled) ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-gray-700">Manual Payment</span>
                        </label>
                    </div>
                </div>

                <div class="md:col-span-3 border-t border-gray-100 pt-4">
                    <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-6">

                        {{-- Paystack --}}
                        <div class="space-y-3">
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-indigo-500 inline-block"></span>Paystack
                            </h3>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Public Key</label>
                                <input type="text" name="paystack_public_key" class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400"
                                    value="{{ $paystackPublic }}">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Secret Key</label>
                                <input type="text" name="paystack_secret_key" class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400"
                                    value="{{ $paystackSecret }}">
                            </div>
                        </div>

                        {{-- Flutterwave --}}
                        <div class="space-y-3">
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-yellow-500 inline-block"></span>Flutterwave
                            </h3>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Public Key</label>
                                <input type="text" name="flutterwave_public_key" class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400"
                                    value="{{ $flutterwavePublic }}">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Secret Key</label>
                                <input type="text" name="flutterwave_secret_key" class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400"
                                    value="{{ $flutterwaveSecret }}">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Encryption Key</label>
                                <input type="text" name="flutterwave_encryption_key" class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400"
                                    value="{{ $flutterwaveEncryptionKey }}">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Redirect URL</label>
                                <input type="text" name="flutterwave_redirect_url" class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400"
                                    value="{{ $flutterwaveRedirectUrl }}">
                            </div>
                        </div>

                        {{-- PaymentPoint --}}
                        <div class="space-y-3">
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>PaymentPoint
                            </h3>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">API Key</label>
                                <input type="text" name="paymentpoint_api_key" class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-green-400 focus:border-green-400"
                                    value="{{ $paymentpointApiKey }}">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Secret Key</label>
                                <input type="text" name="paymentpoint_secret" class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-green-400 focus:border-green-400"
                                    value="{{ $paymentpointSecret }}">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Business ID</label>
                                <input type="text" name="paymentpoint_business_id" class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-green-400 focus:border-green-400"
                                    value="{{ $paymentpointBusinessId }}">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Bank Code</label>
                                <input type="text" name="paymentpoint_bank_code" class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-green-400 focus:border-green-400"
                                    value="{{ $paymentpointBankCode }}">
                            </div>
                        </div>

                        {{-- SprintPay --}}
                        <div class="space-y-3">
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>SprintPay
                            </h3>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Webkey</label>
                                <input type="text" name="sprintpay_webkey" class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                                    value="{{ $sprintpayWebkey }}">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">API Base URL</label>
                                <input type="url" name="sprintpay_base_url" placeholder="https://web.sprintpay.online"
                                    class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                                    value="{{ $sprintpayBaseUrl }}">
                                <p class="text-xs text-gray-400 mt-1">Use <code class="text-gray-500">https://web.sprintpay.online</code> (not web.enkpay.com).</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Webhook Secret</label>
                                <input type="text" name="sprintpay_webhook_secret" class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                                    value="{{ $sprintpayWebhookSecret }}">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Fund URL (webhook)</label>
                                <input type="text" readonly class="w-full border border-gray-200 bg-gray-50 px-3 py-2 rounded-lg text-sm text-gray-600"
                                    value="{{ $sprintpayWebhookUrl }}">
                                <p class="text-xs text-gray-400 mt-1">Set as Fund URL in SprintPay webkey settings.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Verify URL (callback)</label>
                                <input type="text" readonly class="w-full border border-gray-200 bg-gray-50 px-3 py-2 rounded-lg text-sm text-gray-600"
                                    value="{{ $sprintpayCallbackUrl }}">
                                <p class="text-xs text-gray-400 mt-1">Set as Verify URL in SprintPay webkey settings.</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════ --}}
        {{-- SECTION 2: SMS SERVERS                     --}}
        {{-- ═══════════════════════════════════════════ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-800 to-blue-700 px-5 py-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <h2 class="text-sm font-bold text-white">SMS Servers</h2>
            </div>
            <div class="p-5 grid md:grid-cols-2 xl:grid-cols-3 gap-6">

                {{-- DaisySMS --}}
                <div class="border border-gray-200 rounded-xl p-4 space-y-3">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-pink-500 inline-block"></span>DaisySMS — Server 1
                    </h3>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">API Key</label>
                        <input type="text" name="daisysms_api_key"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-pink-400 focus:border-pink-400"
                            value="{{ $daisysmsApiKey }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">USD to Naira Rate</label>
                        <input type="number" step="0.01" name="usd_to_naira_rate"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-pink-400 focus:border-pink-400"
                            value="{{ $usdToNairaRate }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Service Gain (₦)</label>
                        <input type="number" step="0.01" name="service_gain"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-pink-400 focus:border-pink-400"
                            value="{{ $serviceGain }}">
                    </div>
                </div>

                {{-- DaisySIM Virtual Numbers --}}
                <div class="border border-blue-200 bg-blue-50/30 rounded-xl p-4 space-y-3">
                    <h3 class="text-xs font-bold text-blue-600 uppercase tracking-widest flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>DaisySIM — Virtual Numbers
                    </h3>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">API Key</label>
                        <input type="text" name="virtual_api_key"
                            class="w-full border border-gray-300 bg-white px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                            value="{{ $virtualApiKey }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">USD to Naira Rate</label>
                        <input type="number" step="0.01" name="virtual_usd_to_naira_rate"
                            class="w-full border border-gray-300 bg-white px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                            value="{{ $virtualUsdToNairaRate }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Service Gain (₦)</label>
                        <input type="number" step="0.01" name="virtual_service_gain"
                            class="w-full border border-gray-300 bg-white px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                            value="{{ $virtualServiceGain }}">
                    </div>
                </div>

                {{-- OprimeNumbers / Tellabot --}}
                <div class="border border-gray-200 rounded-xl p-4 space-y-3">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-purple-500 inline-block"></span>OprimeNumbers — Server 2
                    </h3>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">USD to Naira Rate</label>
                        <input type="number" step="0.01" name="tellabot_usd_to_naira_rate"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-purple-400 focus:border-purple-400"
                            value="{{ old('tellabot_usd_to_naira_rate', $tellabotRate) }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Service Gain (%)</label>
                        <input type="number" step="0.01" name="tellabot_gain_percent"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-purple-400 focus:border-purple-400"
                            value="{{ old('tellabot_gain_percent', $tellabotGain) }}">
                    </div>
                    <div class="pt-2 border-t border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 mb-2">WhatsApp Pricing</p>
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Base Price (₦)</label>
                                <input type="number" step="0.01" name="whatsapp_price_override"
                                    class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-purple-400"
                                    value="{{ old('whatsapp_price_override', $whatsapp_price_override ?? '') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Markup (%)</label>
                                <input type="number" step="0.01" name="whatsapp_markup_percent"
                                    class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-purple-400"
                                    value="{{ old('whatsapp_markup_percent', $whatsapp_markup_percent ?? '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 mb-2">Telegram Pricing</p>
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Base Price (₦)</label>
                                <input type="number" step="0.01" name="telegram_price_override"
                                    class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-purple-400"
                                    value="{{ old('telegram_price_override', $telegram_price_override ?? '') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Markup (%)</label>
                                <input type="number" step="0.01" name="telegram_markup_percent"
                                    class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-purple-400"
                                    value="{{ old('telegram_markup_percent', $telegram_markup_percent ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SMSMan --}}
                <div class="border border-gray-200 rounded-xl p-4 space-y-3">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-orange-500 inline-block"></span>SMS-Man — Server 4
                    </h3>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">API Token</label>
                        <input type="text" name="smsman_api_token"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-orange-400 focus:border-orange-400"
                            value="{{ $smsmanApiToken }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">USD to Naira Rate</label>
                        <input type="number" step="0.01" name="smsman_usd_to_naira_rate"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-orange-400 focus:border-orange-400"
                            value="{{ $smsmanRate }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Service Gain (₦)</label>
                        <input type="number" step="0.01" name="smsman_service_gain"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-orange-400 focus:border-orange-400"
                            value="{{ $smsmanGain }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Markup Percent (%)</label>
                        <input type="number" step="0.01" name="smsman_markup_percent"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-orange-400 focus:border-orange-400"
                            value="{{ old('smsman_markup_percent', $settings['smsman_markup_percent'] ?? 50) }}">
                    </div>
                </div>

                {{-- SMSPool --}}
                <div class="border border-gray-200 rounded-xl p-4 space-y-3">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-teal-500 inline-block"></span>SMSPool — Server 3/5
                    </h3>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">API Key</label>
                        <input type="text" name="smspool_api_key"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-teal-400 focus:border-teal-400"
                            value="{{ $smspoolApiKey }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">USD to Naira Rate</label>
                        <input type="number" step="0.01" name="smspool_usd_to_naira_rate"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-teal-400 focus:border-teal-400"
                            value="{{ $smspoolRate }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Service Gain (₦)</label>
                        <input type="number" step="0.01" name="smspool_service_gain"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-teal-400 focus:border-teal-400"
                            value="{{ $smspoolGain }}">
                    </div>
                </div>

                {{-- GoGetSMS --}}
                <div class="border border-gray-200 rounded-xl p-4 space-y-3">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-cyan-500 inline-block"></span>GoGetSMS — Server 6
                    </h3>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">API Key</label>
                        <input type="text" name="gogetsms_api_key"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400"
                            value="{{ old('gogetsms_api_key', $gogetsmsApiKey ?? '') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">USD to Naira Rate</label>
                        <input type="number" step="0.01" name="gogetsms_usd_to_naira_rate"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400"
                            value="{{ old('gogetsms_usd_to_naira_rate', $gogetsmsRate ?? 1500) }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Extra Cost per Service (₦)</label>
                        <input type="number" step="0.01" name="gogetsms_extra_cost"
                            class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400"
                            value="{{ old('gogetsms_extra_cost', $gogetsmsExtraCost ?? 0) }}">
                    </div>
                </div>

            </div>
        </div>

        {{-- Save Button (bottom) --}}
        <div class="flex justify-end pb-6">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-lg font-semibold text-sm transition shadow">
                Save All Settings
            </button>
        </div>

    </form>
</div>
@endsection
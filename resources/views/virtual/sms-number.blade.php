@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 dark:from-zinc-900 dark:to-zinc-900 py-4 sm:px-4 lg:px-6">
    <div class="mx-auto space-y-4">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-white">Virtual Numbers</h1>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">Purchase temporary numbers for SMS verification</p>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('error'))
            <div class="bg-red-50 dark:bg-red-950/40 border-l-4 border-red-500 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg shadow-sm">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span class="text-sm">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-50 dark:bg-green-950/40 border-l-4 border-green-500 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg shadow-sm">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="text-sm">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        {{-- USA / Canada Tip --}}
        <div class="bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/50 rounded-xl px-4 py-3">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1">💡 How these numbers work</p>
                    <p class="text-xs text-blue-700 dark:text-blue-400 leading-relaxed">
                        These numbers are valid for both <strong>🇺🇸 USA</strong> and <strong>🇨🇦 Canada</strong> services.
                        If a number does not receive an SMS for a particular service, it may be because that service
                        restricts one country — simply try again and the number may work under the other country.
                        <span class="block mt-1 font-medium">USA and Canada share the same +1 country code, so most services accept both.</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Purchase Form --}}
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-md overflow-hidden border border-slate-200 dark:border-white/10">
            <div class="bg-gradient-to-r from-blue-900 to-blue-800 dark:from-zinc-700 dark:to-zinc-600 px-4 sm:px-6 py-3">
                <h3 class="text-sm font-semibold text-white">Purchase Virtual Number</h3>
            </div>

            <div class="p-4 sm:p-6">

                {{-- Service Search --}}
                <div x-data="smsServiceSelect()" x-init="init()" class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">Service</label>
                    <div class="relative">
                        <button type="button" @click="if(options.length) toggleDropdown()"
                            class="w-full text-left text-sm px-3 py-2.5 rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-zinc-900 flex items-center justify-between"
                            :class="{'opacity-50 cursor-not-allowed': !options.length}">
                            <span class="text-slate-700 dark:text-zinc-300 truncate" x-text="selectedLabel || (options.length ? 'Select service...' : 'Loading services...')"></span>
                            <svg class="w-4 h-4 text-slate-400 flex-shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <input type="hidden" id="service-code" x-model="selectedValue">
                        <input type="hidden" id="service-name" x-model="selectedLabel">

                        <div x-show="isOpen" x-transition @click.away="isOpen=false"
                            class="absolute z-50 w-full mt-1 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-white/10 rounded-lg shadow-lg overflow-hidden">
                            <div class="p-2 border-b border-slate-200 dark:border-white/10">
                                <input type="text" x-model="searchQuery" @input="filterOptions()" placeholder="Search services..."
                                    class="w-full text-sm px-3 py-2 border border-slate-300 dark:border-white/10 rounded-lg bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-200 placeholder:text-slate-400 focus:outline-none" autocomplete="off">
                            </div>
                            <div class="max-h-60 overflow-y-auto">
                                <template x-for="o in filteredOptions" :key="o.value">
                                    <div @click="selectOption(o)"
                                        class="px-3 py-2 text-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 cursor-pointer text-slate-700 dark:text-zinc-300"
                                        x-text="o.label"></div>
                                </template>
                                <div x-show="filteredOptions.length===0" class="px-3 py-4 text-sm text-center text-slate-500 dark:text-zinc-500">No services found</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Price Box --}}
                <div id="priceBox" class="bg-blue-50 dark:bg-zinc-900/60 border-2 border-dashed border-blue-200 dark:border-white/10 rounded-lg py-4 px-4 text-center mb-4">
                    <p class="text-xs text-slate-600 dark:text-zinc-400">Select a service to load available numbers</p>
                </div>

                {{-- FIX: added selected-api-price hidden input --}}
                <input type="hidden" id="selected-pool">
                <input type="hidden" id="selected-ngn">
                <input type="hidden" id="selected-api-price">

                <button id="purchaseBtn" disabled onclick="confirmPurchase()"
                    class="w-full bg-gradient-to-r from-blue-900 to-blue-800 dark:from-blue-700 dark:to-blue-600 text-white text-sm font-semibold py-3 rounded-lg
                    hover:from-blue-800 hover:to-blue-700 dark:hover:from-blue-600 dark:hover:to-blue-500 transition-all duration-200
                    disabled:from-slate-400 disabled:to-slate-400 dark:disabled:from-zinc-600 dark:disabled:to-zinc-600
                    disabled:cursor-not-allowed shadow-lg disabled:shadow-none">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Purchase Number
                    </span>
                </button>
            </div>
        </div>

        {{-- Orders Table --}}
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-md overflow-hidden border border-slate-200 dark:border-white/10">
            <div class="bg-gradient-to-r from-blue-900 to-blue-800 dark:from-zinc-700 dark:to-zinc-600 px-4 sm:px-6 py-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-white">Your Virtual Numbers</h3>
            </div>

            @php $orders = $verifications; @endphp

            {{-- Mobile --}}
            <div class="block sm:hidden divide-y divide-slate-200 dark:divide-white/5">
                @forelse($orders as $o)
                <div data-activation="{{ $o->activation_id }}" class="p-4 space-y-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">{{ $o->service }}</p>
                            <p class="text-xs font-mono text-slate-500 dark:text-zinc-400 mt-0.5">{{ $o->number }}</p>
                            <p class="text-xs text-slate-400 dark:text-zinc-500 mt-0.5">🇺🇸 USA / 🇨🇦 Canada</p>
                        </div>
                        <div class="status-col flex-shrink-0">
                            @if($o->status==='Completed')
                                <span class="px-2 py-1 rounded-md text-xs font-medium bg-green-100 dark:bg-green-950/50 text-green-800 dark:text-green-400">Done</span>
                            @elseif(in_array($o->status,['Cancelled','Expired']))
                                <span class="px-2 py-1 rounded-md text-xs font-medium bg-red-100 dark:bg-red-950/40 text-red-800 dark:text-red-400">{{ $o->status }}</span>
                            @else
                                <span class="px-2 py-1 rounded-md text-xs font-medium bg-amber-100 dark:bg-amber-950/40 text-amber-800 dark:text-amber-400 inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>Waiting
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <div><span class="text-slate-500 dark:text-zinc-500 block mb-0.5">Price</span><span class="font-bold text-amber-600 dark:text-amber-400">₦{{ number_format($o->naira_amount ?? $o->price, 2) }}</span></div>
                        <div><span class="text-slate-500 dark:text-zinc-500 block mb-0.5">Code</span><span class="code-col font-bold text-slate-800 dark:text-white font-mono">{{ $o->code ?? '—' }}</span></div>
                    </div>
                    @if(empty($o->code) && !in_array($o->status,['Cancelled','Expired']))
                        <button onclick="cancelOrder('{{ $o->activation_id }}')"
                            class="cancel-btn w-full mt-1 bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 text-xs font-medium py-2 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors border border-red-200 dark:border-red-900/50">
                            Cancel &amp; Refund
                        </button>
                    @endif
                </div>
                @empty
                <div class="p-8 text-center">
                    <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-zinc-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <p class="text-sm text-slate-500 dark:text-zinc-500">No orders yet</p>
                    <p class="text-xs text-slate-400 dark:text-zinc-600 mt-1">Purchase a number above to get started</p>
                </div>
                @endforelse
            </div>

            {{-- Desktop --}}
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-white/5">
                    <thead class="bg-slate-50 dark:bg-zinc-900/50">
                        <tr>
                            @foreach(['Service','Number','Price (₦)','Status','Code','Date','Action'] as $col)
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wider whitespace-nowrap">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-slate-200 dark:divide-white/5">
                        @forelse($orders as $o)
                        <tr data-activation="{{ $o->activation_id }}" class="hover:bg-slate-50 dark:hover:bg-zinc-700/50 transition-colors">
                            <td class="px-4 py-3 text-sm font-semibold text-slate-800 dark:text-white whitespace-nowrap">{{ $o->service }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-slate-600 dark:text-zinc-400">{{ $o->number }}</td>
                            <td class="px-4 py-3 text-sm font-bold font-mono text-amber-600 dark:text-amber-400">₦{{ number_format($o->naira_amount ?? $o->price, 2) }}</td>
                            <td class="px-4 py-3 status-col whitespace-nowrap">
                                @if($o->status==='Completed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-green-100 dark:bg-green-950/50 text-green-800 dark:text-green-400"><span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>Done</span>
                                @elseif(in_array($o->status,['Cancelled','Expired']))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-red-100 dark:bg-red-950/40 text-red-800 dark:text-red-400"><span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>{{ $o->status }}</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-amber-100 dark:bg-amber-950/40 text-amber-800 dark:text-amber-400"><span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>Waiting</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-bold font-mono text-slate-800 dark:text-white"><span class="code-col">{{ $o->code ?? '—' }}</span></td>
                            <td class="px-4 py-3">
                                <div class="text-xs text-slate-600 dark:text-zinc-300 whitespace-nowrap">{{ \Carbon\Carbon::parse($o->created_at)->format('M d, Y') }}</div>
                                <div class="text-xs text-slate-400 dark:text-zinc-500">{{ \Carbon\Carbon::parse($o->created_at)->format('h:i A') }}</div>
                                <div class="text-xs text-slate-400 dark:text-zinc-600 mt-0.5">{{ \Carbon\Carbon::parse($o->created_at)->diffForHumans() }}</div>
                            </td>
                            <td class="px-4 py-3 action-col">
                                @if(empty($o->code) && !in_array($o->status,['Cancelled','Expired']))
                                    <button onclick="cancelOrder('{{ $o->activation_id }}')"
                                        class="cancel-btn text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-xs font-medium hover:underline whitespace-nowrap">
                                        Cancel &amp; Refund
                                    </button>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-zinc-600">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-zinc-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <p class="text-sm text-slate-500 dark:text-zinc-500">No orders yet</p>
                                <p class="text-xs text-slate-400 dark:text-zinc-600 mt-1">Purchase a number above to get started</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
const CSRF      = document.querySelector('meta[name="csrf-token"]').content;
const isDark    = () => document.documentElement.classList.contains('dark');
const swalTheme = () => ({ background: isDark() ? '#27272a' : '#ffffff', color: isDark() ? '#f4f4f5' : '#1e293b' });

const SMS_SERVICES = @json(collect($services)->map(fn($s) => ['value' => $s['code'], 'label' => $s['name']])->values());

// ── Alpine: Service ────────────────────────────────────────────────────────
function smsServiceSelect() {
    return {
        options: SMS_SERVICES,
        filteredOptions: SMS_SERVICES,
        isOpen: false,
        selectedValue: '',
        selectedLabel: '',
        searchQuery: '',
        init() {},
        toggleDropdown() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) { this.searchQuery = ''; this.filteredOptions = this.options; }
        },
        filterOptions() {
            const q = this.searchQuery.toLowerCase();
            this.filteredOptions = this.options.filter(o => o.label.toLowerCase().includes(q));
        },
        selectOption(o) {
            this.selectedValue = o.value;
            this.selectedLabel = o.label;
            this.isOpen = false;
            document.getElementById('service-code').value = o.value;
            document.getElementById('service-name').value = o.label;
            loadPrice(o.value);
        }
    };
}

// ── Load prices ────────────────────────────────────────────────────────────
async function loadPrice(serviceCode) {
    resetPrice();
    const box = document.getElementById('priceBox');
    document.getElementById('purchaseBtn').disabled = true;

    box.innerHTML = `
        <div class="flex items-center justify-center gap-2">
            <svg class="animate-spin h-4 w-4 text-blue-900 dark:text-blue-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span class="text-xs text-slate-600 dark:text-zinc-400">Loading available numbers...</span>
        </div>`;

    try {
        const r = await fetch('/sms-number/prices', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            body: JSON.stringify({ service: serviceCode }),
        });
        const data = await r.json();

        if (!data.success || !Array.isArray(data.available_pools) || !data.available_pools.length) {
            box.innerHTML = `<p class="text-xs text-red-600 dark:text-red-400 text-center">${data.message ?? 'No numbers available for this selection'}</p>`;
            return;
        }

        const dark        = isDark();
        const routeLabels = ['Standard','Economy','Premium','Express','Basic','Pro','Ultra','Lite','Plus','Max'];
        let html = `<p class="text-xs font-semibold ${dark?'text-zinc-400':'text-slate-600'} uppercase tracking-widest mb-3">Select Available Number</p><div class="grid gap-2">`;

        data.available_pools.forEach((pool, index) => {
            const label = routeLabels[index] ?? `Route ${index + 1}`;
            html += `
                <div class="pool-card cursor-pointer border-2 ${dark?'border-zinc-600 bg-zinc-800 hover:border-blue-500 hover:bg-blue-900/20':'border-slate-200 bg-white hover:border-blue-900 hover:bg-blue-50'} rounded-lg px-4 py-3 transition-all"
                     onclick="selectPool(this)"
                     data-pool="${pool.pool}"
                     data-ngn="${pool.ngn_price}"
                     data-api-price="${pool.api_price}">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-md ${dark?'bg-blue-900/40':'bg-blue-100'} flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 ${dark?'text-blue-400':'text-blue-900'}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold ${dark?'text-white':'text-slate-800'}">${label}</p>
                                <p class="text-xs ${dark?'text-zinc-400':'text-slate-500'}">${pool.available} available</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold ${dark?'text-amber-400':'text-amber-600'} text-sm font-mono">₦${parseFloat(pool.ngn_price).toLocaleString('en-NG',{minimumFractionDigits:2,maximumFractionDigits:2})}</p>
                        </div>
                    </div>
                </div>`;
        });

        html += `</div>`;
        box.innerHTML = html;

    } catch (e) {
        box.innerHTML = `<p class="text-xs text-red-600 dark:text-red-400 text-center">Failed to load pricing</p>`;
    }
}

// ── Select pool ────────────────────────────────────────────────────────────
function selectPool(el) {
    const dark = isDark();
    document.querySelectorAll('.pool-card').forEach(c => {
        c.classList.remove('border-blue-900','border-blue-500','bg-blue-50','bg-blue-900/20');
        c.classList.add(dark ? 'border-zinc-600' : 'border-slate-200');
    });
    el.classList.remove(dark ? 'border-zinc-600' : 'border-slate-200');
    el.classList.add(dark ? 'border-blue-500' : 'border-blue-900', dark ? 'bg-blue-900/20' : 'bg-blue-50');

    document.getElementById('selected-pool').value      = el.dataset.pool;
    document.getElementById('selected-ngn').value       = el.dataset.ngn;
    document.getElementById('selected-api-price').value = el.dataset.apiPrice; // FIX: store raw provider price
    document.getElementById('purchaseBtn').disabled     = false;
}

// ── Confirm + Purchase ─────────────────────────────────────────────────────
let _purchasing = false;

async function confirmPurchase() {
    if (_purchasing) return;

    const pool        = document.getElementById('selected-pool').value;
    const ngn         = document.getElementById('selected-ngn').value;
    const apiPrice    = document.getElementById('selected-api-price').value; // FIX: read api_price
    const serviceCode = document.getElementById('service-code').value;
    const serviceName = document.getElementById('service-name').value;

    // FIX: guard includes apiPrice
    if (!pool || !ngn || !apiPrice || !serviceCode) {
        Swal.fire({ icon: 'warning', title: 'Select a route', text: 'Please select a service and route first.', confirmButtonColor: '#1e3a8a', ...swalTheme() });
        return;
    }

    const result = await Swal.fire({
        title: 'Confirm Purchase',
        html: `<p class="text-sm" style="color:${isDark()?'#a1a1aa':'#64748b'}">You are about to purchase a number for</p>
               <p class="font-bold text-lg mt-1" style="color:${isDark()?'#60a5fa':'#1e3a8a'}">${serviceName}</p>
               <div style="background:${isDark()?'#3f3f46':'#f1f5f9'};border-radius:8px;padding:10px 16px;margin-top:12px;display:inline-block">
                   <p style="font-family:monospace;font-weight:700;font-size:20px;color:${isDark()?'#f59e0b':'#d97706'}">₦${parseFloat(ngn).toLocaleString('en-NG',{minimumFractionDigits:2,maximumFractionDigits:2})}</p>
               </div>
               <p class="text-xs mt-3" style="color:${isDark()?'#71717a':'#94a3b8'}">Works for both 🇺🇸 USA and 🇨🇦 Canada services. Amount will be deducted from your wallet immediately.</p>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Purchase',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#1e3a8a',
        reverseButtons: true,
        ...swalTheme(),
    });

    if (!result.isConfirmed) return;

    _purchasing = true;
    const btn = document.getElementById('purchaseBtn');
    btn.disabled = true;
    btn.innerHTML = `<span class="flex items-center justify-center gap-2"><svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>Processing…</span>`;
    Swal.fire({ title: 'Processing…', allowOutsideClick: false, didOpen: () => Swal.showLoading(), ...swalTheme() });

    try {
        const r = await fetch('/sms-number/purchase', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            // FIX: send api_price so controller validation passes
            body: JSON.stringify({
                service:      serviceCode,
                service_name: serviceName,
                pool:         parseInt(pool),
                api_price:    parseFloat(apiPrice),
            }),
        });
        const j = await r.json();

        if (!j.success) {
            _purchasing = false;
            btn.disabled = false;
            btn.innerHTML = `<span class="flex items-center justify-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Purchase Number</span>`;
            Swal.fire({ icon: 'error', title: 'Purchase Failed', text: j.error || j.message || 'Something went wrong.', confirmButtonColor: '#dc2626', ...swalTheme() });
            return;
        }

        const d = j.data;
        prependOrderRow(d);
        startPolling(d.activation_id);

        Swal.fire({
            icon: 'success',
            title: 'Number Purchased!',
            html: `<p class="text-sm" style="color:${isDark()?'#a1a1aa':'#64748b'}">Your number is ready. Waiting for SMS code.</p>
                   <div style="background:${isDark()?'#3f3f46':'#f1f5f9'};border-radius:8px;padding:10px 16px;margin-top:12px">
                       <p style="font-family:monospace;font-size:16px;font-weight:700;letter-spacing:2px;color:${isDark()?'#60a5fa':'#1e3a8a'}">${d.phone_number}</p>
                       <p style="font-size:11px;color:${isDark()?'#71717a':'#94a3b8'};margin-top:4px">${d.service}</p>
                   </div>
                   <p style="font-size:11px;color:${isDark()?'#71717a':'#94a3b8'};margin-top:8px">💡 If SMS doesn't arrive, try using this number under the other country (USA ↔ Canada).</p>`,
            confirmButtonText: "OK, I'll wait",
            confirmButtonColor: '#1e3a8a',
            ...swalTheme(),
        });

        _purchasing = false;
        btn.disabled = false;
        btn.innerHTML = `<span class="flex items-center justify-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Purchase Number</span>`;
        resetPrice();

    } catch {
        _purchasing = false;
        btn.disabled = false;
        Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not complete purchase. Please try again.', confirmButtonColor: '#dc2626', ...swalTheme() });
    }
}

function resetPrice() {
    document.getElementById('priceBox').innerHTML = `<p class="text-xs text-slate-600 dark:text-zinc-400">Select a service to load available numbers</p>`;
    document.getElementById('selected-pool').value      = '';
    document.getElementById('selected-ngn').value       = '';
    document.getElementById('selected-api-price').value = ''; // FIX: clear api_price too
    document.getElementById('purchaseBtn').disabled     = true;
}

// ── Prepend row ────────────────────────────────────────────────────────────
function prependOrderRow(d) {
    const ngn      = parseFloat(d.ngn_charged).toLocaleString('en-NG',{minimumFractionDigits:2,maximumFractionDigits:2});
    const now      = new Date();
    const dateStr  = now.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});
    const timeStr  = now.toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit'});
    const waitBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-amber-100 dark:bg-amber-950/40 text-amber-800 dark:text-amber-400"><span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>Waiting</span>`;

    // Desktop
    const tbody = document.querySelector('table tbody');
    if (tbody) {
        const emptyRow = tbody.querySelector('td[colspan]');
        if (emptyRow) emptyRow.closest('tr').remove();

        const tr = document.createElement('tr');
        tr.dataset.activation = d.activation_id;
        tr.className = 'hover:bg-slate-50 dark:hover:bg-zinc-700/50 transition-colors';
        tr.innerHTML = `
            <td class="px-4 py-3 text-sm font-semibold text-slate-800 dark:text-white whitespace-nowrap">${d.service}</td>
            <td class="px-4 py-3 text-sm font-mono text-slate-600 dark:text-zinc-400">${d.phone_number}</td>
            <td class="px-4 py-3 text-sm font-bold font-mono text-amber-600 dark:text-amber-400">₦${ngn}</td>
            <td class="px-4 py-3 status-col whitespace-nowrap">${waitBadge}</td>
            <td class="px-4 py-3 text-sm font-bold font-mono text-slate-800 dark:text-white"><span class="code-col">—</span></td>
            <td class="px-4 py-3"><div class="text-xs text-slate-600 dark:text-zinc-300 whitespace-nowrap">${dateStr}</div><div class="text-xs text-slate-400 dark:text-zinc-500">${timeStr}</div></td>
            <td class="px-4 py-3 action-col"><button onclick="cancelOrder('${d.activation_id}')" class="cancel-btn text-red-600 dark:text-red-400 hover:text-red-800 text-xs font-medium hover:underline whitespace-nowrap opacity-50 cursor-not-allowed" disabled>Cancel (3:00)</button></td>`;
        tbody.prepend(tr);
    }

    // Mobile
    const mobileList = document.querySelector('.block.sm\\:hidden.divide-y');
    if (mobileList) {
        const emptyDiv = mobileList.querySelector('.p-8');
        if (emptyDiv) emptyDiv.remove();

        const div = document.createElement('div');
        div.dataset.activation = d.activation_id;
        div.className = 'p-4 space-y-2.5';
        div.innerHTML = `
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">${d.service}</p>
                    <p class="text-xs font-mono text-slate-500 dark:text-zinc-400 mt-0.5">${d.phone_number}</p>
                    <p class="text-xs text-slate-400 dark:text-zinc-500 mt-0.5">🇺🇸 USA / 🇨🇦 Canada</p>
                </div>
                <div class="status-col flex-shrink-0">${waitBadge}</div>
            </div>
            <div class="grid grid-cols-3 gap-2 text-xs">
                <div><span class="text-slate-500 dark:text-zinc-500 block mb-0.5">Price</span><span class="font-bold text-amber-600 dark:text-amber-400">₦${ngn}</span></div>
                <div><span class="text-slate-500 dark:text-zinc-500 block mb-0.5">Code</span><span class="code-col font-bold font-mono text-slate-800 dark:text-white">—</span></div>
            </div>
            <button onclick="cancelOrder('${d.activation_id}')" class="cancel-btn w-full mt-1 bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 text-xs font-medium py-2 rounded-lg border border-red-200 dark:border-red-900/50 opacity-50 cursor-not-allowed" disabled>Cancel (3:00)</button>`;
        mobileList.prepend(div);
    }
}

// ── Mark row done ──────────────────────────────────────────────────────────
function markRowDone(id, code) {
    const doneBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-green-100 dark:bg-green-950/50 text-green-800 dark:text-green-400"><span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>Done</span>`;
    document.querySelectorAll(`[data-activation="${id}"]`).forEach(row => {
        row.querySelectorAll('.status-col').forEach(el => el.innerHTML = doneBadge);
        row.querySelectorAll('.code-col').forEach(el => el.textContent = code ?? '');
        row.querySelectorAll('.cancel-btn').forEach(el => el.remove());
        row.querySelectorAll('.action-col').forEach(el => el.innerHTML = '<span class="text-xs text-slate-400 dark:text-zinc-600">—</span>');
    });
}

// ── Cancel countdown ───────────────────────────────────────────────────────
const _cancelTimers    = {};
const _cancelDeadlines = {};

function _renderCountdown(id) {
    const deadline = _cancelDeadlines[id];
    if (!deadline) return;

    const remaining = Math.max(0, Math.ceil((deadline - Date.now()) / 1000));
    const mins = Math.floor(remaining / 60);
    const secs = String(remaining % 60).padStart(2, '0');

    document.querySelectorAll(`[data-activation="${id}"] .cancel-btn`).forEach(btn => {
        if (remaining > 0) {
            btn.textContent = `Cancel (${mins}:${secs})`;
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            btn.textContent = 'Cancel & Refund';
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    });

    if (remaining === 0) {
        clearInterval(_cancelTimers[id]);
        delete _cancelTimers[id];
        delete _cancelDeadlines[id];
    }
}

function startCancelCountdown(id, remainingSeconds = 180) {
    _cancelDeadlines[id] = Date.now() + remainingSeconds * 1000;
    document.querySelectorAll(`[data-activation="${id}"] .cancel-btn`).forEach(btn => {
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
    });
    _renderCountdown(id);
    _cancelTimers[id] = setInterval(() => _renderCountdown(id), 500);
}

document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
        Object.keys(_cancelDeadlines).forEach(id => _renderCountdown(id));
    }
});

// ── Poll for code ──────────────────────────────────────────────────────────
function startPolling(id, remaining = 180) {
    startCancelCountdown(id, remaining);

    const interval = setInterval(async () => {
        try {
            const r = await fetch(`/sms-number/check/${id}/poll`, { cache: 'no-store' });
            const j = await r.json();

            if (j.status === 'done' && j.code) {
                clearInterval(interval);
                if (_cancelTimers[id]) { clearInterval(_cancelTimers[id]); delete _cancelTimers[id]; }
                delete _cancelDeadlines[id];

                markRowDone(id, j.code);

                await Swal.fire({
                    icon: 'success',
                    title: '✅ Code Received!',
                    html: `<div class="text-center">
                               <p style="font-size:13px;color:${isDark()?'#a1a1aa':'#64748b'};margin-bottom:12px">Your verification code is:</p>
                               <div style="background:${isDark()?'#3f3f46':'#f1f5f9'};border-radius:10px;padding:12px 24px;display:inline-block">
                                   <span style="font-family:monospace;font-weight:700;font-size:28px;letter-spacing:6px;color:${isDark()?'#60a5fa':'#1e3a8a'}">${j.code}</span>
                               </div>
                           </div>`,
                    confirmButtonText: 'Copy Code',
                    showCancelButton: true,
                    cancelButtonText: 'Close',
                    confirmButtonColor: '#1e3a8a',
                    reverseButtons: true,
                    timer: 20000,
                    timerProgressBar: true,
                    ...swalTheme(),
                }).then(res => {
                    if (res.isConfirmed) {
                        navigator.clipboard.writeText(j.code).then(() =>
                            Swal.fire({ icon: 'success', title: 'Copied!', timer: 1200, showConfirmButton: false, ...swalTheme() })
                        );
                    }
                });
            }
        } catch { /* silent */ }
    }, 3000);
}

// ── Cancel order ───────────────────────────────────────────────────────────
async function cancelOrder(id) {
    const row = document.querySelector(`[data-activation="${id}"]`);
    if (row) {
        const codeEl = row.querySelector('.code-col');
        if (codeEl && codeEl.textContent.trim() !== '—' && codeEl.textContent.trim() !== '') {
            Swal.fire({ icon: 'info', title: 'Cannot Cancel', text: 'A verification code has already been received for this order.', confirmButtonColor: '#1e3a8a', ...swalTheme() });
            return;
        }
    }

    const result = await Swal.fire({
        title: 'Cancel Order?',
        text: 'Are you sure you want to cancel? You will be refunded if no code was received.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, cancel it',
        cancelButtonText: 'No, keep it',
        reverseButtons: true,
        ...swalTheme(),
    });

    if (!result.isConfirmed) return;

    Swal.fire({ title: 'Processing...', text: 'Cancelling order and processing refund...', allowOutsideClick: false, didOpen: () => Swal.showLoading(), ...swalTheme() });

    try {
        const r = await fetch(`/sms-number/cancel/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const j = await r.json();

        if (j.success) {
            document.querySelectorAll(`[data-activation="${id}"]`).forEach(el => {
                el.style.transition = 'opacity .25s ease, transform .25s ease';
                el.style.opacity    = '0';
                el.style.transform  = 'translateX(16px)';
                setTimeout(() => el.remove(), 300);
            });

            await Swal.fire({
                icon: 'success',
                title: 'Order Cancelled!',
                html: `<p style="font-size:13px;color:${isDark()?'#a1a1aa':'#475569'}">${j.message ?? 'Order cancelled successfully.'}</p>
                       <div style="background:${isDark()?'#052e16':'#f0fdf4'};border:1px solid ${isDark()?'#166534':'#bbf7d0'};border-radius:8px;padding:12px;margin-top:12px">
                           <p style="font-size:11px;font-weight:600;color:${isDark()?'#4ade80':'#15803d'};margin-bottom:4px">Refund Details</p>
                           <p style="font-size:13px;color:${isDark()?'#86efac':'#166634'}">Refunded: <strong>₦${(parseFloat(j.data?.refund_amount ?? j.data?.refund ?? 0)).toLocaleString('en-NG',{minimumFractionDigits:2})}</strong></p>
                           <p style="font-size:13px;color:${isDark()?'#86efac':'#166634'}">New Balance: <strong>₦${(parseFloat(j.data?.wallet_balance ?? j.data?.balance_after ?? 0)).toLocaleString('en-NG',{minimumFractionDigits:2})}</strong></p>
                       </div>`,
                confirmButtonText: 'OK',
                confirmButtonColor: '#16a34a',
                ...swalTheme(),
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Failed', text: j.error || j.message || 'Could not cancel order.', confirmButtonColor: '#dc2626', ...swalTheme() });
        }
    } catch {
        Swal.fire({ icon: 'error', title: 'Network Error', text: 'Something went wrong. Please try again.', confirmButtonColor: '#dc2626', ...swalTheme() });
    }
}

// ── Auto-start polling on page load ───────────────────────────────────────
@foreach($verifications as $v)
@if(empty($v->code) && !in_array($v->status, ['Cancelled', 'Expired', 'Completed']))
(function() {
    const id      = '{{ $v->activation_id }}';
    const created = {{ \Carbon\Carbon::parse($v->created_at)->timestamp }};
    const now     = Math.floor(Date.now() / 1000);
    const remaining = Math.max(0, 180 - (now - created));

    if (remaining > 0) {
        startPolling(id, remaining);
    } else {
        document.querySelectorAll(`[data-activation="${id}"] .cancel-btn`).forEach(btn => {
            btn.textContent = 'Cancel & Refund';
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
        });
        startPolling(id, 0);
    }
})();
@endif
@endforeach
</script>
@endsection
@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-8">
    <h2 class="text-2xl font-bold text-blue-700 mb-6">📱 Server 5 – SMS-Activate.ae (🇺🇸 USA only)</h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded mb-4">{{ session('error') }}</div>
    @endif
    @if(session('charged_usd') && session('charged_ngn'))
        <div class="bg-blue-100 text-blue-800 p-4 rounded mb-4">
            💵 Required: ${{ session('charged_usd') }} (₦{{ number_format(session('charged_ngn'), 2) }})
        </div>
    @endif

    <div class="bg-white rounded-xl shadow p-6">
        <form method="POST" action="{{ route('server5.purchase') }}">
            @csrf

            <!-- Always USA -->
            <input type="hidden" name="country" id="country-select" value="187">
            <input type="hidden" name="resolved_country" id="resolved-country" value="187">
            <input type="hidden" name="locked_price" id="locked-price">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold mb-2">Country</label>
                    <div class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded">🇺🇸 United States</div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Service</label>
                    <select name="service" id="service-select" class="w-full border border-gray-300 rounded px-4 py-2" required>
                        <option value="">Select service</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Price (NGN)</label>
                    <input type="text"
                           id="price"
                           name="maxPrice"
                           class="w-full border border-gray-300 rounded px-4 py-2 bg-gray-100"
                           readonly>
                    <p class="text-xs text-gray-500 mt-1" id="price-detail"></p>
                </div>
            </div>

            <button type="submit" id="purchase-btn"
                class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded disabled:opacity-50"
                disabled>
                Purchase Number
            </button>
        </form>
    </div>

    @if($verifications->count())
        <div class="mt-10">
            <h3 class="text-lg font-bold mb-4 text-gray-800">📄 Your Verifications</h3>
            <div class="overflow-x-auto border border-gray-200 rounded-xl">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-100 font-semibold text-gray-700 uppercase">
                        <tr>
                            <th class="px-4 py-3">Service</th>
                            <th class="px-4 py-3">Number</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Code</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($verifications as $v)
                            <tr id="row-{{ $v->id }}">
                                <td class="px-4 py-2">{{ $v->name ?? strtoupper($v->service) }}</td>
                                <td class="px-4 py-2">{{ $v->number ?? '—' }}</td>
                                <td class="px-4 py-2" id="status-{{ $v->id }}">{{ $v->status }}</td>
                                <td class="px-4 py-2" id="code-{{ $v->id }}">{{ $v->code ?? '—' }}</td>
                                <td class="px-4 py-2" id="action-{{ $v->id }}">
                                    @if($v->status !== 'Completed')
                                        <span id="cancel-btn-{{ $v->id }}" class="text-gray-400 text-sm italic" data-created="{{ $v->created_at->timestamp }}">
                                            Cancel (wait 2 mins)
                                        </span>
                                    @else
                                        <span class="text-green-600 text-sm">Done</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const serviceSelect = document.getElementById('service-select');
    const priceField = document.getElementById('price');
    const submitBtn = document.getElementById('purchase-btn');
    const detail = document.getElementById('price-detail');
    const resolvedInput = document.getElementById('resolved-country');
    const lockedPriceInput = document.getElementById('locked-price');
    const markup = 50;
    const rate = 1500;
    const country = '187'; // USA

    async function fetchOptions(route, select, labelKey = 'name', valueKey = 'code') {
        const res = await fetch(route);
        const data = await res.json();
        const entries = Object.entries(data.status === 'success' ? data.services || {} : data);

        entries.sort((a, b) => {
            const nameA = (a[1][labelKey] || '').toString();
            const nameB = (b[1][labelKey] || '').toString();
            return nameA.localeCompare(nameB);
        });

        entries.forEach(([key, val]) => {
            const option = document.createElement('option');
            option.value = valueKey ? (val[valueKey] || key) : key;
            option.text = val[labelKey] || val.eng || key;
            select.appendChild(option);
        });
    }

    async function loadPrice() {
        const service = serviceSelect.value;

        if (!service) {
            priceField.value = '';
            detail.textContent = '';
            submitBtn.disabled = true;
            return;
        }

        try {
            const res = await fetch(`{{ route('server5.live-price') }}?service=${service}&country=${country}`);
            const data = await res.json();

            if (data.usd) {
                const usd = parseFloat(data.usd);
                const usdWithMarkup = usd * (1 + markup / 100);
                const ngn = Math.round(usdWithMarkup * rate);

                resolvedInput.value = '187';
                lockedPriceInput.value = usd;
                priceField.value = ngn;

                detail.textContent = `USD: $${usd.toFixed(2)} + ${markup}% = $${usdWithMarkup.toFixed(2)} | NGN: ₦${ngn} | Available: ${data.count ?? '?'}`;
                submitBtn.disabled = false;
            } else {
                priceField.value = 'N/A';
                detail.textContent = 'Price not found.';
                submitBtn.disabled = true;
            }
        } catch (e) {
            priceField.value = 'Error';
            detail.textContent = 'Error fetching price.';
            submitBtn.disabled = true;
        }
    }

    await fetchOptions("{{ route('server5.services') }}", serviceSelect, 'name', 'code');
    serviceSelect.addEventListener('change', loadPrice);

    // Poll every 5 seconds for code updates
    setInterval(() => {
        @foreach($verifications as $v)
            @if($v->status !== 'Completed')
                fetch(`{{ url('/verifications/server5/poll-code/' . $v->id) }}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            document.getElementById('status-{{ $v->id }}').textContent = data.status;
                            document.getElementById('code-{{ $v->id }}').textContent = data.code || '—';

                            if (data.status === 'Completed') {
                                const actionTd = document.getElementById('action-{{ $v->id }}');
                                actionTd.innerHTML = `<span class="text-green-600 text-sm">Done</span>`;
                            }
                        }
                    });
            @endif
        @endforeach
    }, 5000);
});
</script>
@endsection

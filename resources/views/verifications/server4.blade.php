@extends('layouts.app')

@section('content')
<h2 class="text-3xl font-extrabold text-indigo-600 mb-8">📲 Server 4 Verifications</h2>

<!--{{-- Success/Error Messages --}}-->
<!--@if(session('success'))-->
<!--    <div class="bg-green-100 text-green-800 px-6 py-4 rounded-lg mb-6 shadow-lg border border-green-200">{{ session('success') }}</div>-->
<!--@endif-->
<!--@if(session('error'))-->
<!--    <div class="bg-red-100 text-red-800 px-6 py-4 rounded-lg mb-6 shadow-lg border border-red-200">{{ session('error') }}</div>-->
<!--@endif-->

{{-- Purchase Form --}}
<div class="bg-white rounded-lg shadow-xl p-8 mb-12">
    <form method="POST" action="{{ route('server4.purchase') }}" id="purchaseForm" class="space-y-6">
        @csrf
        <div class="grid md:grid-cols-2 gap-8">
            {{-- Country Search + Dropdown --}}
            <div class="relative">
                <label for="countrySearch" class="block text-lg font-semibold text-gray-700 mb-2">🔍 Search Country</label>
                <input type="text" id="countrySearch" placeholder="Type to search..." class="w-full border border-gray-300 px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <select name="country" id="country" class="w-full mt-2 border border-gray-300 px-4 py-3 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    <option value="">-- Choose Country --</option>
                    @foreach($countries as $country)
                        <option value="{{ $country['id'] }}">{{ $country['name'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Service Search + Dropdown --}}
            <div class="relative">
                <label for="serviceSearch" class="block text-lg font-semibold text-gray-700 mb-2">🔍 Search Service</label>
                <input type="text" id="serviceSearch" placeholder="Type to search..." class="w-full border border-gray-300 px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" disabled>
                <select name="service" id="service" class="w-full mt-2 border border-gray-300 px-4 py-3 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required disabled>
                    <option value="">-- Choose Service --</option>
                    @foreach($services as $service)
                        <option value="{{ $service['id'] }}">{{ $service['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="priceDisplay" class="text-2xl font-semibold text-indigo-700 mt-4"></div>

        <button type="submit" id="purchaseBtn" class="w-full bg-gradient-to-r from-indigo-600 to-indigo-700 text-white py-3 rounded-xl hover:from-indigo-700 hover:to-indigo-800 transition duration-300 shadow-lg transform hover:scale-105">
            🚀 Purchase Number
        </button>
    </form>
</div>
<div 
    x-data="{ 
        activeSlide: 0, 
        slides: [
            { image: 'https://www.mailer.socialaccslog.com/wp-content/uploads/2025/05/web-banner2.png', url: 'https://wa.me/2349064818798' },
            { image: 'https://www.mailer.socialaccslog.com/wp-content/uploads/2025/05/web-banner2.png', url: 'https://wa.me/2349064818798' },
            { image: 'https://www.mailer.socialaccslog.com/wp-content/uploads/2025/05/web-banner2.png', url: 'https://wa.me/2349064818798' }
        ],
        init() {
            setInterval(() => {
                this.activeSlide = (this.activeSlide + 1) % this.slides.length;
            }, 3000);
        }
    }" 
    class="relative w-full max-w-3xl mx-auto overflow-hidden rounded-2xl shadow-lg mt-6 mb-8" 
    style="height: 140px;"
>
    <template x-for="(slide, index) in slides" :key="index">
        <div 
            x-show="activeSlide === index" 
            class="absolute inset-0 transition-all duration-500"
            x-transition:enter="transform ease-out duration-300"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transform ease-in duration-300"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="-translate-x-full opacity-0"
        >
            <a :href="slide.url" target="_blank">
                <img :src="slide.image" class="w-full h-full object-cover" alt="Slide">
            </a>
        </div>
    </template>
</div>
{{-- Verifications Table --}}
<div class="bg-white rounded-lg shadow-xl p-8">
    <h3 class="text-2xl font-bold text-gray-900 mb-6">📋 Recent Server 4 Verifications</h3>
    @php
        $countryMap = collect($countries)->pluck('name', 'id');
        $serviceMap = collect($services)->pluck('name', 'id');
    @endphp
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
            <thead class="bg-indigo-50 text-gray-700 uppercase text-xs font-semibold">
                <tr>
                    <th class="px-6 py-4 text-left">📞 Number</th>
                    <th class="px-6 py-4 text-left">🔧 Service</th>
                    <th class="px-6 py-4 text-left">🌐 Country</th>
                    <th class="px-6 py-4 text-left">💵 Price</th>
                    <th class="px-6 py-4 text-left">📈 Status</th>
                    <th class="px-6 py-4 text-left">💬 Code</th>
                    <th class="px-6 py-4 text-left">⚙️ Action</th>
                </tr>
            </thead>
            <tbody id="verifications-table" class="divide-y divide-gray-200 bg-white">
                @foreach(\App\Models\Verification::where('user_id', auth()->id())->where('server', 4)->latest()->get() as $v)
                    <tr data-id="{{ $v->id }}" class="hover:bg-indigo-50 transition duration-150">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $v->number }}</td>
                        <td class="px-6 py-4">{{ $serviceMap[$v->service] ?? 'Unknown' }}</td>
                        <td class="px-6 py-4">{{ $countryMap[$v->country_id] ?? 'Unknown' }}</td>
                        <td class="px-6 py-4 font-mono">₦{{ number_format($v->naira_amount, 2) }}</td>
                        <td class="px-6 py-4 status">
                            @if($v->status === 'Received')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-600 text-white text-xs font-bold">
                                    {{ $v->status }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-yellow-500 text-white text-xs font-bold">
                                    {{ $v->status }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 code">{{ $v->code ?? 'Waiting...' }}</td>
                        <td class="px-6 py-4 action">
                            @if($v->code)
                                <span class="text-green-600 font-semibold">✅ Completed</span>
                            @else
                                <div x-data="{ submitting: false, hidden: false }">
                                    <form method="GET" action="{{ route('server4.cancel', $v->id) }}" x-show="!hidden" @submit.prevent="if (!submitting) {
                                        submitting = true;
                                        hidden = true;
                                        window.location.href = $el.action;
                                        setTimeout(() => hidden = false, 5000);
                                    }">
                                        <button
                                            type="submit"
                                            x-bind:disabled="submitting"
                                            x-bind:class="submitting ? 'opacity-60 cursor-not-allowed' : ''"
                                            class="bg-red-600 text-white py-1 px-3 rounded-lg hover:bg-red-700 transition duration-200"
                                        >
                                            <span x-show="!submitting">Cancel</span>
                                            <span x-show="submitting">Processing...</span>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const priceDisplay = document.getElementById('priceDisplay');
    const countrySelect = document.getElementById('country');
    const serviceSelect = document.getElementById('service');
    const purchaseBtn = document.getElementById('purchaseBtn');
    const purchaseForm = document.getElementById('purchaseForm');
    
    countrySelect.addEventListener('change', function () {
        const hasCountry = this.value;
        serviceSelect.disabled = !hasCountry;
        document.getElementById('serviceSearch').disabled = !hasCountry;
    });

    purchaseForm.addEventListener('submit', function (event) {
        event.preventDefault();
        if (confirm('Are you sure you want to purchase this number?')) {
            purchaseBtn.disabled = true;
            purchaseBtn.innerText = 'Processing...';
            purchaseBtn.classList.add('opacity-60', 'cursor-not-allowed');
            purchaseForm.submit(); // Continue with form submission
        }
    });

    serviceSelect.addEventListener('change', function () {
        const country = countrySelect.value;
        const service = this.value;
        if (!country || !service) return;

        priceDisplay.innerText = 'Fetching price...';
        fetch(`{{ route('server4.get-price') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ country, service })
        })
        .then(res => res.json())
        .then(data => {
            priceDisplay.innerText = data.success
                ? `💰 Estimated Price: ₦${data.price_naira.toFixed(2)}`
                : (data.message || '❌ Could not fetch price.');
        })
        .catch(() => priceDisplay.innerText = '❌ Error fetching price.');
    });

    function filterOptions(searchInputId, selectId) {
        const input = document.getElementById(searchInputId);
        const select = document.getElementById(selectId);
        const originalOptions = Array.from(select.options);

        input.addEventListener('input', function () {
            const filter = this.value.toLowerCase();
            select.innerHTML = '';
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.text = '-- Choose --';
            select.appendChild(defaultOption);

            originalOptions.forEach(option => {
                if (option.text.toLowerCase().includes(filter)) {
                    select.appendChild(option.cloneNode(true));
                }
            });
        });
    }

    filterOptions('countrySearch', 'country');
    filterOptions('serviceSearch', 'service');

    setInterval(() => {
        document.querySelectorAll('#verifications-table tr').forEach(row => {
            const id = row.getAttribute('data-id');
            const statusCell = row.querySelector('.status');
            const codeCell = row.querySelector('.code');
            const actionCell = row.querySelector('.action');

            if (statusCell.innerText.trim().toLowerCase() === 'received' || codeCell.innerText.trim() !== 'Waiting...') return;

            fetch(`/verifications/server4/check/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status && data.status !== statusCell.innerText) {
                        statusCell.innerHTML =
                            data.status === 'Received'
                                ? `<span class="inline-flex items-center px-3 py-1 rounded-full bg-green-600 text-white text-xs font-bold">${data.status}</span>`
                                : `<span class="inline-flex items-center px-3 py-1 rounded-full bg-yellow-500 text-white text-xs font-bold">${data.status}</span>`;
                    }
                    if (data.code && data.code !== codeCell.innerText) {
                        codeCell.innerText = data.code;
                        if (data.status === 'Received') {
                            actionCell.innerHTML = '<span class="text-green-600 font-semibold">✅ Completed</span>';
                        }
                    }
                })
                .catch(console.error);
        });
    }, 5000);
});
</script>
<script>
    @if(session('message') || session('success'))
        Swal.fire({
            title: 'Success!',
            text: @js(session('success') ?? session('message')),
            icon: 'success',
            confirmButtonColor: '#5D4037',
            confirmButtonText: 'OK'
        });
    @endif

    @if(session('error'))


        Swal.fire({
            title: 'Error!',
            text: @js(session('error')),
            icon: 'error',
            confirmButtonColor: '#B71C1C',
            confirmButtonText: 'OK'
        });
    @endif

    @if($errors->any())
        Swal.fire({
            title: 'Validation Error',
            html: @js(implode('<br>', $errors->all())),
            icon: 'warning',
            confirmButtonColor: '#FF5722',
            confirmButtonText: 'Got it'
        });
    @endif
</script>
@endpush

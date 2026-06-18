@extends('layouts.app')

@section('content')
<div x-data="modalHandler()" x-init="init()">


<!-- Header Section -->
<div class="pb-8 border-b border-gray-200/50">
    <div class="flex items-center gap-4">
        <div class="p-3 bg-blue-100 rounded-xl">
            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Server 3 Verifications</h1>
            <p class="text-gray-500 mt-1">Premium virtual numbers for seamless verification</p>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<!--<div class="mt-6 space-y-3">-->
<!--    @if(session('success'))-->
<!--        <div class="flex items-center p-4 bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm">-->
<!--            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">-->
<!--                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>-->
<!--            </svg>-->
<!--            <span class="ml-3 text-emerald-700">{{ session('success') }}</span>-->
<!--        </div>-->
<!--    @endif-->

<!--    @if(session('error'))-->
<!--        <div class="flex items-center p-4 bg-red-50 border border-red-200 rounded-xl shadow-sm">-->
<!--            <svg class="w-5 h-5 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">-->
<!--                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>-->
<!--            </svg>-->
<!--            <span class="ml-3 text-red-700">{{ session('error') }}</span>-->
<!--        </div>-->
<!--    @endif-->
<!--</div>-->

<!-- Purchase Card -->
<div class="mt-8 bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
    <h2 class="text-xl font-semibold text-gray-900 mb-6">Get New Number</h2>
    <form method="POST" action="{{ route('server3.purchase') }}" id="purchaseForm">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Country Select -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Select Country
                </label>
                @php $groupedCountries = collect($countries)->groupBy('region'); @endphp
                <select name="country" id="country" class="ts-country" required>
                    <option value="">Choose Country</option>
                    @foreach($groupedCountries as $region => $items)
                        <optgroup label="{{ $region }}">
                            @foreach($items as $country)
                                <option value="{{ $country['ID'] }}">{{ $country['name'] }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <!-- Service Select -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Select Service
                </label>
                <select name="service" id="service" class="ts-service" required disabled>
                    <option value="">Choose Service</option>
                    @foreach($services as $service)
                        <option value="{{ $service['ID'] }}">{{ $service['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Price Display -->
        <div id="priceDisplay" class="mt-6 p-4 bg-blue-50 rounded-xl transition-all duration-300 opacity-0">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-lg font-semibold text-blue-800"></span>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" id="purchaseBtn" class="mt-6 w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-3 px-6 rounded-xl font-semibold shadow-md transition-all">
            <div class="flex items-center justify-center">
                <svg id="loadingSpinner" class="w-5 h-5 mr-2 animate-spin hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                <span>Purchase Number</span>
            </div>
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
<!-- Verifications Table -->
<div class="mt-8 bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
    <div class="px-6 py-5 bg-gray-50 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Recent Verifications</h3>
    </div>
    @php
        $serviceMap = collect($services)->pluck('name', 'ID');
        $countryMap = collect($countries)->pluck('name', 'ID');
    @endphp

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-100 text-xs uppercase text-gray-600">
                <tr>
                    <th class="px-6 py-4 font-medium">Number</th>
                    <th class="px-6 py-4 font-medium">Service</th>
                    <th class="px-6 py-4 font-medium">Country</th>
                    <th class="px-6 py-4 font-medium">Price</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium">Code</th>
                    <th class="px-6 py-4 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200" id="verifications-table">
                @foreach(\App\Models\Verification::where('user_id', auth()->id())->where('name', 'Server 3')->latest()->get() as $v)
                <tr class="hover:bg-gray-50 transition-colors" data-id="{{ $v->id }}">
                    <td class="px-6 py-4 font-mono text-gray-900">{{ $v->number }}</td>
                    <td class="px-6 py-4 text-gray-700">{{ $serviceMap[$v->service] ?? 'Unknown' }}</td>
                    <td class="px-6 py-4 text-gray-700">{{ $countryMap[$v->country_id] ?? 'Unknown' }}</td>
                    <td class="px-6 py-4 font-mono text-gray-900">₦{{ number_format($v->naira_amount, 0) }}</td>
                    <td class="px-6 py-4">
                        <span class="status inline-flex items-center px-3 py-1 rounded-full text-xs font-medium 
                            {{ $v->status === 'Received' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $v->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 font-mono text-gray-900 code">{{ $v->code ?? '---' }}</td>
                    <td class="px-6 py-4 action">
                        @if($v->code)
                            <span class="text-green-600 font-medium">Completed</span>
                        @else
                            <a href="{{ route('server3.cancel', $v->id) }}" 
   class="text-red-600 hover:text-red-800 transition-colors cancel-link"
   @click.prevent="actionType = 'cancel'; actionUrl = '{{ route('server3.cancel', $v->id) }}'; showModal = true;">

                                Cancel
                            </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<!-- Confirmation Modal -->
<div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div @click.away="showModal = false" class="bg-white rounded-xl shadow-lg p-6 w-full max-w-sm mx-auto">
        <h2 class="text-lg font-semibold text-gray-800 mb-4" x-text="actionType === 'purchase' ? 'Confirm Purchase' : 'Confirm Cancellation'"></h2>
        <p class="text-gray-600 mb-6">
            <span x-show="actionType === 'purchase'">Are you sure you want to purchase this number?</span>
            <span x-show="actionType === 'cancel'">Are you sure you want to cancel this verification?</span>
        </p>
        <div class="flex justify-end gap-4">
            <button @click="showModal = false" class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50">No</button>
            <button @click="handleModalAction()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Yes</button>
        </div>
    </div>
</div>

</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<style>
.ts-country, .ts-service {
    --ts-pr: 3rem;
    --ts-border-radius: 0.75rem;
    --ts-spacing: 0.875rem;
}
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
function modalHandler() {
    return {
        showModal: false,
        actionType: '',
        actionUrl: '',

        init() {
            // Global functions to trigger modals
            window.showPurchaseModal = () => {
                this.actionType = 'purchase';
                this.showModal = true;
            };

            window.showCancelModal = (url) => {
                this.actionType = 'cancel';
                this.actionUrl = url;
                this.showModal = true;
            };

            window.handleModalAction = () => {
                if (this.actionType === 'purchase') {
                    document.getElementById('purchaseForm').submit();
                } else if (this.actionType === 'cancel') {
                    window.location.href = this.actionUrl;
                }
                this.showModal = false;
            };
        }
    };
}

document.addEventListener('DOMContentLoaded', function () {
    // Country/Service selectors + price handling
    const countrySelect = document.getElementById('country');
    const serviceSelect = document.getElementById('service');
    const priceDisplay = document.getElementById('priceDisplay');
    const priceText = priceDisplay.querySelector('span');

    const fetchPrice = () => {
        const service = serviceSelect.value;
        const country = countrySelect.value;
        

        if (!service || !country) return;

        priceDisplay.classList.remove('opacity-0');
        priceText.textContent = 'Loading...';

        fetch('{{ route('server3.get-price') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ service, country })
        })
        .then(res => res.json())
        .then(data => {
            priceText.textContent = data.success
                ? `Estimated Price: ₦${data.price_naira.toLocaleString()}`
                : 'Price unavailable';
        })
        .catch(() => {
            priceText.textContent = 'Error fetching price';
        });
    };

    serviceSelect.addEventListener('change', fetchPrice);

    // TomSelect
    const initTomSelect = (selector) => new TomSelect(selector, {
        create: false,
        sortField: { field: 'text', direction: 'asc' },
        render: {
            option: (data, escape) => `<div class="flex items-center p-3 hover:bg-blue-50">${data.text}</div>`,
            item: (data, escape) => `<div class="flex items-center bg-blue-50 text-blue-800 px-3 py-1 rounded-lg">${data.text}</div>`
        }
    });

    const countryTom = initTomSelect('.ts-country');
    const serviceTom = initTomSelect('.ts-service');

    countrySelect.addEventListener('change', function () {
        serviceSelect.disabled = !this.value;
        this.value ? serviceTom.enable() : serviceTom.disable();
    });

    // SMS Polling
    setInterval(() => {
        document.querySelectorAll('#verifications-table tr').forEach(row => {
            const id = row.dataset.id;
            const statusEl = row.querySelector('.status');
            const statusText = statusEl?.textContent?.trim().toLowerCase();

            if (!statusEl || statusText === 'received' || statusText === 'completed') return;

            fetch(`/verifications/server3/check/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.code) {
                        row.querySelector('.code').textContent = data.code;
                        statusEl.textContent = 'Received';
                        statusEl.className = 'status inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800';
                        row.querySelector('.action').innerHTML = '<span class="text-green-600 font-medium">Completed</span>';
                    }
                });
        });
    }, 3000);

    // Form Submit opens modal
    const purchaseForm = document.getElementById('purchaseForm');
    purchaseForm.addEventListener('submit', function (e) {
        e.preventDefault();
        window.showPurchaseModal();
    });

    // Cancel links open modal
    document.querySelectorAll('.cancel-link').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const cancelUrl = this.getAttribute('href');
            window.showCancelModal(cancelUrl);
        });
    });
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

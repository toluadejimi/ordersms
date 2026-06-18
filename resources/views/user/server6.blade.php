@extends('layouts.app')

@section('content')
<div class="mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-3">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-3 rounded-lg shadow">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                Server 6
            </h2>
        </div>
        <p class="mt-2 text-gray-600">Purchase virtual numbers for SMS verification</p>
    </div>

    <!-- Messages -->
    <!--@if(session('success'))-->
    <!--<div class="flex items-center bg-emerald-50 text-emerald-700 px-4 py-3 rounded-lg mb-6 transition-all duration-300">-->
    <!--    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">-->
    <!--        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>-->
    <!--    </svg>-->
    <!--    {{ session('success') }}-->
    <!--</div>-->
    <!--@endif-->
    <!--@if(session('error'))-->
    <!--<div class="flex items-center bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-6 transition-all duration-300">-->
    <!--    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">-->
    <!--        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>-->
    <!--    </svg>-->
    <!--    {{ session('error') }}-->
    <!--</div>-->
    <!--@endif-->

    <!-- Purchase Card -->
    <div class="bg-white rounded-2xl shadow-xl p-6 mb-10 border border-gray-100">
        <form method="POST" action="{{ route('server6.purchase') }}" id="purchase-form">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <!-- Country -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                    <div class="relative">
                        <select id="country" name="country" class="w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 rounded-xl shadow-sm">
                            <option value="">Select Country</option>
                            @foreach($countries as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Service -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Service</label>
                    <div class="relative">
                        <select id="service-select" name="service" class="w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 rounded-xl shadow-sm">
                            <option value="">Select Service</option>
                        </select>
                    </div>
                </div>

                <!-- Price -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                    <div class="relative">
                        <input type="text" id="price" class="w-full px-4 py-3 text-base border-gray-300 rounded-xl shadow-sm bg-gray-50 font-medium text-blue-600" readonly>
                    </div>
                </div>
            </div>

            <button type="button" id="buyBtn" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium py-4 px-6 rounded-xl shadow-lg transition-all duration-300 transform hover:scale-[1.02]">
                Purchase Number
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
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Active Verifications – Server 6</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px]">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($verifications as $v)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-800 font-medium">{{ $v->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $v->number }}</td>
                            <td class="px-6 py-4 text-sm text-blue-600 font-medium">₦{{ number_format($v->naira_amount, 2) }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'Waiting' => 'bg-yellow-100 text-yellow-800',
                                        'Completed' => 'bg-emerald-100 text-emerald-800',
                                        'Cancelled' => 'bg-red-100 text-red-800'
                                    ];
                                @endphp
                                <span id="status-{{ $v->id }}" class="px-3 py-1 rounded-full text-xs font-medium {{ $statusColors[$v->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $v->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 font-mono" id="code-{{ $v->id }}">{{ $v->code ?? '—' }}</td>
                            <td class="px-6 py-4">
                                @if(!in_array($v->status, ['Completed', 'Cancelled']))
                                    <form method="POST" action="{{ route('server6.cancel', $v->id) }}" class="cancel-form">
                                        @csrf
                                        <button type="button" class="text-red-600 hover:text-red-800 text-sm font-medium transition-colors cancel-btn">
                                            Cancel
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p>No verifications found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Scripts -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const countrySelect = document.getElementById('country');
    const serviceSelectEl = document.getElementById('service-select');
    const priceInput = document.getElementById('price');
    const purchaseForm = document.getElementById('purchase-form');
    const cancelBtns = document.querySelectorAll('.cancel-btn');

    const countryTomSelect = new TomSelect('#country', {
        placeholder: 'Select Country',
        allowEmptyOption: true,
        persist: false,
        create: false,
        sortField: { field: 'text', direction: 'asc' }
    });

    const serviceTomSelect = new TomSelect('#service-select', {
        placeholder: 'Select Service',
        allowEmptyOption: true,
        persist: false,
        create: false,
        sortField: { field: 'text', direction: 'asc' }
    });

    countrySelect.addEventListener('change', function () {
        const countryId = this.value;
        serviceTomSelect.clearOptions();
        priceInput.value = '';

        fetch(`/server6/services/${countryId}`)
            .then(res => res.json())
            .then(data => {
                const options = [];
                Object.entries(data).forEach(([code, info]) => {
                    options.push({
                        value: code,
                        text: `${info.name} – ₦${parseFloat(info.price_ngn).toFixed(2)} (${info.count} available)`,
                        price: info.price_ngn
                    });
                });
                serviceTomSelect.addOptions(options);
                serviceTomSelect.refreshOptions(false);
            });
    });

    serviceTomSelect.on('change', function (value) {
        const selected = serviceTomSelect.options[value];
        const price = selected?.price ?? '0';
        priceInput.value = `₦${parseFloat(price).toFixed(2)}`;
    });

    document.getElementById('buyBtn')?.addEventListener('click', function () {
        if (confirm('Are you sure you want to purchase this number?')) {
            this.disabled = true;
            this.innerText = 'Processing...';
            purchaseForm.submit();
        }
    });

    cancelBtns.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            if (confirm('Are you sure you want to cancel this verification?')) {
                this.closest('form').submit();
            }
        });
    });

    function pollStatuses() {
        document.querySelectorAll('[id^="status-"]').forEach(el => {
            const id = el.id.replace('status-', '');
            const statusEl = document.getElementById(`status-${id}`);
            const codeEl = document.getElementById(`code-${id}`);

            if (statusEl.textContent.trim() !== 'Waiting') return;

            fetch(`/server6/check-status/${id}`)
                .then(res => res.json())
                .then(data => {
                    const cancelForm = document.querySelector(`form[action$="/${id}"]`);
                    const td = cancelForm?.closest('td');

                    if (data.status === 'Completed') {
                        statusEl.textContent = 'Completed';
                        codeEl.textContent = data.code;
                        if (cancelForm && td) {
                            cancelForm.remove();
                            td.innerHTML = '<span class="text-gray-400">—</span>';
                        }
                    } else if (data.status === 'Cancelled') {
                        statusEl.textContent = 'Cancelled';
                        if (cancelForm && td) {
                            cancelForm.remove();
                            td.innerHTML = '<span class="text-gray-400">—</span>';
                        }
                    }
                })
                .catch(err => console.error('Polling error:', err));
        });
    }

    setInterval(pollStatuses, 5000);
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
@endsection

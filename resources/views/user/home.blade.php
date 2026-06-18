@extends('layouts.app')

@section('content')
<div class="w-full mb-10 space-y-4">


    <!-- Header -->


    <div class="text-center w-full px-4 sm:px-6 lg:px-8 mb-16">
        <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight sm:text-5xl">
            <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">USA Number Portal</span>
        </h1>
        <p class="mt-4 text-lg text-gray-500">Premium virtual numbers for seamless verification</p>
    </div>

    <!-- Flash Messages -->
    <!--<div class="max-w-3xl mx-auto mb-10 space-y-4">-->
    <!--    @if (session('success'))-->
    <!--        <div x-data="{ show: true }" x-show="show" x-transition-->
    <!--            class="flex items-center p-4 bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm">-->
    <!--            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">-->
    <!--                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>-->
    <!--            </svg>-->
    <!--            <span class="ml-3 text-emerald-700 flex-1">{{ session('success') }}</span>-->
    <!--            <button @click="show = false" class="ml-4 text-emerald-700 hover:text-emerald-900">-->
    <!--                <span class="sr-only">Close</span>-->
    <!--                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
    <!--                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>-->
    <!--                </svg>-->
    <!--            </button>-->
    <!--        </div>-->
    <!--    @endif-->

    <!--    @if (session('error'))-->
    <!--        <div x-data="{ show: true }" x-show="show" x-transition-->
    <!--            class="flex items-center p-4 bg-red-50 border border-red-200 rounded-xl shadow-sm">-->
    <!--            <svg class="w-5 h-5 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">-->
    <!--                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>-->
    <!--            </svg>-->
    <!--            <span class="ml-3 text-red-700 flex-1">{{ session('error') }}</span>-->
    <!--            <button @click="show = false" class="ml-4 text-red-700 hover:text-red-900">-->
    <!--                <span class="sr-only">Close</span>-->
    <!--                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
    <!--                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>-->
    <!--                </svg>-->
    <!--            </button>-->
    <!--        </div>-->
    <!--    @endif-->
    <!--</div>-->

    <!-- Purchase Card -->
    <div class="w-full px-4 sm:px-6 lg:px-8">

        <h2 class="text-2xl font-bold text-gray-900 mb-8">Get New Number</h2>
 <!-- ✅ updated form to dispatch modal -->
       <form method="POST" action="{{ route('purchase.number') }}" 
      x-data 
      @submit.prevent="$dispatch('open-modal', { action: 'purchase', form: $el })">

            @csrf
            
            <div class="space-y-6">
                <!-- Service Search -->
                <div class="relative">
                    <label for="searchService" class="block text-sm font-medium text-gray-700 mb-2">Search Services</label>
                    <div class="relative">
                        <input type="text" id="searchService" placeholder="Search services..."
                            class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Service Select -->
                <div>
                    <label for="service" class="block text-sm font-medium text-gray-700 mb-2">Select Service</label>
                    <div class="relative">
                        <select name="service" id="service" required
                            class="w-full pl-3 pr-10 py-3 border border-gray-300 rounded-lg appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="" disabled selected>Choose a service...</option>
                            @foreach ($services as $s)
                                <option value="{{ $s['service'] }}" data-name="{{ $s['name'] }}" data-price="{{ $s['price'] }}">
                                    {{ $s['name'] }} — ₦{{ number_format($s['price'], 2) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Service Details -->
                <div id="serviceDetails" class="bg-blue-50 p-4 rounded-lg transition-opacity duration-300 opacity-0">
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-blue-700">Service Name</dt>
                            <dd id="displayService" class="mt-1 text-gray-900 font-medium">--</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-blue-700">Price</dt>
                            <dd id="displayPrice" class="mt-1 text-gray-900 font-mono">--</dd>
                        </div>
                    </dl>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-3 px-6 rounded-lg font-semibold transition-all transform hover:scale-[1.02] disabled:opacity-50 disabled:hover:scale-100">
                    <div class="flex items-center justify-center">
                        <svg id="loadingSpinner" class="w-5 h-5 mr-2 animate-spin hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <span id="btnText">Purchase Number</span>
                    </div>
                </button>

            </div>
        </form>
    </div>

    <!-- Verifications Card -->
  <div class="w-full px-4 sm:px-6 lg:px-8">
        <h2 class="text-lg font-semibold mb-4">Your Verifications</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border border-gray-200 rounded">
               
                <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-2">Service</th>
                        <th class="px-4 py-2">Number</th>
                        <th class="px-4 py-2">Price</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Code</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 divide-y divide-gray-100">
                    @forelse ($verifications as $v)
                        <tr id="verification-{{ $v->id }}">
                            <td class="px-4 py-2">{{ strtoupper($v->name ?? $v->service) }}</td>
                            <td class="px-4 py-2 font-mono">{{ $v->number }}</td>
                            <td class="px-4 py-2 font-mono">₦{{ number_format($v->price, 2) }}</td>
                            <td class="px-4 py-2">
                                <span id="status-{{ $v->id }}"
                                    class="inline-block px-2 py-1 rounded text-xs font-medium
                                    {{ $v->status === 'done' ? 'bg-green-200 text-green-800' : ($v->status === 'active' ? 'bg-yellow-200 text-yellow-800' : 'bg-gray-200 text-gray-800') }}">
                                    {{ $v->status === 'done' ? 'Received' : ucfirst($v->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 font-mono" id="code-{{ $v->id }}">{{ $v->code ?? '-' }}</td>
                            <td class="px-4 py-2" id="actions-{{ $v->id }}">
                                @if ($v->status === 'done')
                                    <span class="text-green-700 font-semibold">Received</span>
                                @elseif ($v->status === 'active')
                                   <a href="#" class="text-red-600 hover:underline"
                                        @click.prevent="$dispatch('open-modal', { action: 'cancel', confirmUrl: '{{ route('cancel', $v->id) }}' })">
                                        Cancel
                                    </a>

                                @else
                                    <span class="text-gray-500">--</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 text-gray-500">No verifications yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $verifications->links('pagination::tailwind') }}
        </div>
       <div x-data="confirmationModal"
             x-show="open"
             x-cloak
             x-transition
             class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4"
                    x-text="action === 'purchase' ? 'Confirm Purchase' : 'Cancel Verification'"></h2>
                <p class="text-gray-700 mb-6"
                    x-text="action === 'purchase'
                        ? 'Are you sure you want to purchase this number?'
                        : 'Are you sure you want to cancel this verification?'"></p>

                <div class="flex justify-end gap-3">
                    <button @click="open = false"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm text-gray-700">
                        No
                    </button>
                    <button @click="submitAction"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded">
                       Yes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('confirmationModal', () => ({
    open: false,
    action: null,
    confirmUrl: '',
    form: null,

    submitAction() {
        if (this.action === 'purchase' && this.form) {
            this.form.submit(); // ✅ Submit the correct form
        } else if (this.action === 'cancel' && this.confirmUrl) {
            window.location.href = this.confirmUrl;
        }
    },

    init() {
        window.addEventListener('open-modal', (e) => {
            this.action = e.detail.action;
            this.confirmUrl = e.detail.confirmUrl || '';
            this.form = e.detail.form || null;
            this.open = true;
        });
    }
}));


    });

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchService');
        const serviceSelect = document.getElementById('service');
        const serviceOptions = Array.from(serviceSelect.querySelectorAll('option')).slice(1);

        searchInput?.addEventListener('input', function () {
            const term = this.value.toLowerCase();
            const filtered = serviceOptions.filter(opt => opt.textContent.toLowerCase().includes(term));
            serviceSelect.innerHTML = '<option value="" disabled selected>Choose a service...</option>';
            filtered.forEach(opt => serviceSelect.appendChild(opt));
        });

        serviceSelect?.addEventListener('change', function () {
            const detailsPanel = document.getElementById('serviceDetails');
            const selected = this.options[this.selectedIndex];
            if (selected.value) {
                detailsPanel.classList.remove('opacity-0');
                document.getElementById('displayService').textContent = selected.dataset.name;
                document.getElementById('displayPrice').textContent = parseFloat(selected.dataset.price).toFixed(2);
            } else {
                detailsPanel.classList.add('opacity-0');
            }
        });

        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function (e) {
                const btn = this.querySelector('button[type="submit"]');
                const spinner = this.querySelector('#loadingSpinner');
                btn.disabled = true;
                spinner?.classList.remove('hidden');
                btn.querySelector('#btnText').textContent = 'Processing...';
            });
        });

        @foreach($verifications as $v)
            @if($v->status === 'active')
                const timer{{ $v->id }} = setInterval(() => {
                    fetch(`/poll-code/{{ $v->id }}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'done') {
                                document.getElementById(`code-{{ $v->id }}`).textContent = data.code;
                                document.getElementById(`status-{{ $v->id }}`).className =
                                    'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800';
                                document.getElementById(`status-{{ $v->id }}`).textContent = 'Received';
                                document.getElementById(`actions-{{ $v->id }}`).innerHTML =
                                    '<span class="text-green-600 font-medium">Completed</span>';
                                clearInterval(timer{{ $v->id }});
                            }
                        });
                }, 3000);
            @endif
        @endforeach
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
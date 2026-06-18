@extends('layouts.app')

@section('content')
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded mb-6 shadow-md">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded mb-6 shadow-md">
            {{ session('error') }}
        </div>
    @endif

    <!-- Purchase Card -->
    <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Purchase New Verification (Server 2)</h3>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('server2.buy') }}" id="purchase-form">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Service</label>
                        <select name="service" id="service-select" required
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Search or select a service...</option>
                           @foreach($services as $service)
                                <option value="{{ $service['name'] }}">
                                    {{ $service['name'] }} - ₦{{ number_format($service['price_ngn'], 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" id="purchase-button"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                            Purchase Now
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Verifications Table -->
    <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Recent Server 2 Verifications</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
               @forelse($verifications as $verification)


                        <tr id="row-{{ $verification->id }}" class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $verification->service }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500" id="number-{{ $verification->id }}">
                                {{ $verification->number ?? 'Awaiting allocation...' }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900" id="code-{{ $verification->id }}">
                                @if($verification->status === 'Completed')
                                    {{ $verification->code }}
                                @else
                                    <span class="text-gray-400">Pending</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm" id="status-{{ $verification->id }}">
                                @php
                                    $statusColors = [
                                        'Completed' => 'bg-green-100 text-green-800',
                                        'Waiting' => 'bg-yellow-100 text-yellow-800',
                                        'Reserved' => 'bg-blue-100 text-blue-800',
                                        'Cancelled' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $statusColors[$verification->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $verification->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                ₦{{ number_format($verification->price, 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $verification->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">
                                @if(in_array($verification->status, ['Waiting', 'Reserved']))
                                    <a href="{{ route('server2.cancel', $verification->id) }}"
                                       class="text-red-600 hover:text-red-900 cancel-btn"
                                       data-id="{{ $verification->id }}">
                                        Cancel
                                    </a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 text-sm">
                                No verifications found. Start by purchasing a new verification.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
    </div>
</div>

{{-- TomSelect --}}
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>



<script>
    new TomSelect('#service-select', {
        placeholder: 'Search or select a service...',
        maxOptions: 999,
    });

    // Disable Purchase button after first click and add confirmation
    document.getElementById('purchase-form').addEventListener('submit', function (e) {
        const btn = document.getElementById('purchase-button');
        e.preventDefault(); // Prevent the form from submitting immediately

        // Show confirmation prompt
        if (confirm("Are you sure you want to purchase this verification?")) {
            btn.disabled = true;
            btn.innerHTML = 'Processing...';
            this.submit(); // Submit the form after confirmation
        }
    });

    // Disable Cancel button after first click and add confirmation
    document.querySelectorAll('.cancel-btn').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault(); // Prevent immediate navigation

            // Show confirmation prompt
            if (confirm("Are you sure you want to cancel this verification?")) {
                // Disable the cancel button and show loading text
                if (!this.classList.contains('disabled')) {
                    this.classList.add('disabled');
                    this.innerText = 'Cancelling...';
                    window.location.href = this.href; // Proceed with the cancel action
                }
            }
        });
    });

    // Polling Script for Server 2
    let pollingData = @json($pollingData);

    if (pollingData.length > 0) {
        setInterval(() => {
            pollingData.forEach(v => {
                fetch(`/server2/read-sms/id/${v.activation_id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.code) {
                            document.getElementById(`code-${v.id}`).innerText = data.code;
                            document.getElementById(`status-${v.id}`).innerHTML = `<span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>`;
                            document.querySelector(`#row-${v.id} td:last-child`).innerHTML = '<span class="text-gray-400">-</span>';
                            pollingData = pollingData.filter(item => item.id !== v.id);
                        }
                    });

                fetch(`/server2/status/${v.id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status) {
                            const statusText = data.status;
                            const badgeClass = {
                                Completed: 'bg-green-100 text-green-800',
                                Waiting: 'bg-yellow-100 text-yellow-800',
                                Reserved: 'bg-blue-100 text-blue-800',
                                Cancelled: 'bg-red-100 text-red-800',
                            }[statusText] || 'bg-gray-100 text-gray-800';

                            document.getElementById(`status-${v.id}`).innerHTML = `<span class="px-3 py-1 rounded-full text-xs font-medium ${badgeClass}">${statusText}</span>`;

                            if (statusText === 'Completed' && data.code) {
                                document.getElementById(`code-${v.id}`).innerText = data.code;
                                document.querySelector(`#row-${v.id} td:last-child`).innerHTML = '<span class="text-gray-400">-</span>';
                                pollingData = pollingData.filter(item => item.id !== v.id);
                            }
                        }
                    });
            });
        }, 5000);
    }
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

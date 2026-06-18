@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto mt-10 p-6 bg-white rounded-xl shadow">
    <h2 class="text-xl font-bold mb-4">PVAPins Verification</h2>

    <form method="GET" action="{{ route('pvapins.index') }}" class="mb-4">
        <label class="block text-sm font-medium mb-1">Country:</label>
        <select name="country" onchange="this.form.submit()" class="border p-2 rounded w-full">
            <option value="usa" {{ $country == 'usa' ? 'selected' : '' }}>USA</option>
            <option value="uk" {{ $country == 'uk' ? 'selected' : '' }}>UK</option>
            <!-- Add more -->
        </select>
    </form>

    <form method="POST" action="{{ route('pvapins.purchase') }}">
        @csrf
        <input type="hidden" name="country" value="{{ $country }}">

        <label class="block text-sm font-medium mb-1">Service:</label>
        <select name="app" class="border p-2 rounded w-full mb-4">
            @if(is_array($rates) && count($rates))
                @foreach($rates as $service => $data)
                    <option value="{{ $service }}">
                        {{ strtoupper($service) }} - ₦{{ $data['price'] ?? 'N/A' }}
                    </option>
                @endforeach
            @else
                <option disabled>No services available for {{ strtoupper($country) }}</option>
            @endif
        </select>

        <button class="bg-blue-600 text-white px-4 py-2 rounded w-full">Purchase Number</button>
    </form>

    @if(session('order'))
        @php $order = session('order'); @endphp

        <div class="mt-6 p-4 border rounded bg-gray-50">
            <p><strong>Number:</strong> {{ $order['number'] ?? 'N/A' }}</p>
            <p><strong>Country:</strong> {{ request('country') }}</p>
            <p><strong>Service:</strong> {{ request('app') }}</p>
            <p><strong>Status:</strong> <span id="sms-status">Waiting SMS...</span></p>
            <p><strong>Code:</strong> <span id="sms-code">--</span></p>
        </div>

        <script>
            const number = "{{ $order['number'] ?? '' }}";
            const app = "{{ request('app') }}";
            const country = "{{ request('country') }}";

            setInterval(() => {
                fetch(`/pvapins/sms/${number}/${country}/${app}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'ok') {
                            document.getElementById('sms-code').innerText = data.code || 'N/A';
                            document.getElementById('sms-status').innerText = "Received";
                        }
                    });
            }, 5000);
        </script>
    @endif
</div>
@endsection

@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Pending Manual Fundings</h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @forelse ($requests as $funding)
        <div class="border border-gray-200 rounded-lg mb-6 p-4">
            <p><strong>User ID:</strong> {{ $funding->user_id }}</p>
            <p><strong>Amount:</strong> ₦{{ number_format($funding->amount, 2) }}</p>
            <p><strong>Reference:</strong> {{ $funding->reference ?? 'N/A' }}</p>
            <p><strong>Note:</strong> {{ $funding->note ?? 'None' }}</p>

            @if ($funding->proof)
                <div class="my-2">
                    <button onclick="document.getElementById('proof-{{ $funding->id }}').classList.toggle('hidden')" 
                            class="text-blue-600 underline">View Receipt</button>

                    <div id="proof-{{ $funding->id }}" class="hidden mt-2">
                        <a href="{{ asset('storage/' . $funding->proof) }}" target="_blank">
                            <img src="{{ asset('/public/storage/' . $funding->proof) }}" class="w-48 rounded shadow border">

                        </a>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.manual-fundings.approve', $funding->id) }}" class="inline-block mr-2">
                @csrf
                <button class="bg-green-600 text-white px-4 py-2 rounded">Approve</button>
            </form>

            <form method="POST" action="{{ route('admin.manual-fundings.reject', $funding->id) }}" class="inline-block mt-2">
                @csrf
                <textarea name="note" placeholder="Reason for rejection" required
                          class="w-full border mt-2 p-2 rounded text-sm mb-2" rows="2"></textarea>
                <button class="bg-red-600 text-white px-4 py-2 rounded">Reject</button>
            </form>
        </div>
    @empty
        <p class="text-gray-600">No pending manual fundings.</p>
    @endforelse
</div>
@endsection

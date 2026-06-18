@extends('layouts.app')

@section('content')


    <h1 class="text-3xl font-bold text-center text-blue-700 mb-8"> Wallet Transactions</h1>
  @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    @if (session()->has('message'))
    <div class="alert alert-success">
        {{ session()->get('message') }}
    </div>
    @endif
    @if (session()->has('error'))
    <div class="alert alert-danger">
        {{ session()->get('error') }}
    </div>
    @endif

    @if ($transactions->count())
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left text-gray-700">
                    <thead class="bg-blue-50 text-gray-600 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4">Method</th>
                            <th class="px-6 py-4">Amount</th>
                            <th class="px-6 py-4">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach ($transactions as $txn)
                            <tr class="hover:bg-blue-50 transition duration-150">
                                <td class="px-6 py-4 font-medium">
                                    <span class="inline-flex items-center gap-2">
                                        @if($txn->type === 'credit')
                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" /></svg>
                                        @else
                                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" /></svg>
                                        @endif
                                        {{ ucfirst($txn->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 capitalize text-blue-600">
                                    {{ $txn->method ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 font-semibold {{ $txn->type === 'debit' ? 'text-red-600' : 'text-green-600' }}">
                                    ₦{{ number_format($txn->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-gray-500">
                                    {{ $txn->created_at->format('d M Y, h:i A') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    @else
        <div class="text-center text-gray-500 text-sm mt-12">
            <p>No transactions found.</p>
        </div>
    @endif
</div>
@endsection

@extends('layouts.admin')

@section('content')
<div class="px-4 py-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">All Transaction History</h1>

    <!-- Search Form -->
    <form method="GET" action="{{ route('admin.transactions.index') }}" class="mb-6 flex flex-col md:flex-row md:items-center gap-4">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search by user email or method"
               class="w-full md:w-1/3 px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:outline-none text-sm text-gray-700" />

        <button type="submit"
                class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm">
            Search
        </button>
    </form>
</div>


    <!-- Table -->
    <div class="bg-white shadow rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                <tr>
                    <th class="px-6 py-3">User Email</th>
                    <th class="px-6 py-3">Type</th>
                    <th class="px-6 py-3">Method</th>
                    <th class="px-6 py-3">Amount</th>
                    <th class="px-6 py-3">Date</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse ($transactions as $txn)
                    <tr>
                        <td class="px-6 py-4">{{ $txn->user->email }}</td>
                        <td class="px-6 py-4 capitalize">{{ $txn->type }}</td>
                        <td class="px-6 py-4 capitalize">{{ $txn->method ?? 'N/A' }}</td>
                        <td class="px-6 py-4 font-semibold {{ $txn->type === 'debit' ? 'text-red-600' : 'text-green-600' }}">
                            ₦{{ number_format($txn->amount, 2) }}
                        </td>
                        <td class="px-6 py-4">{{ $txn->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $transactions->withQueryString()->links('pagination::tailwind') }}
    </div>
@endsection

@extends('layouts.admin')

@section('content')
<div class="px-4 py-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">All Verified Numbers</h1>

    <!-- Search Form -->
    <form method="GET" action="{{ route('admin.verifications.index') }}" class="mb-6">
        <div class="flex items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by email or service"
                   class="w-full md:w-1/3 border border-gray-300 rounded px-4 py-2 text-sm focus:ring focus:ring-indigo-500 focus:outline-none" />
            <button type="submit"
                    class="bg-indigo-600 text-white px-4 py-2 text-sm rounded hover:bg-indigo-700 transition">
                Search
            </button>
        </div>
    </form>
</div>
    <div class="bg-white shadow rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                <tr>
                    <th class="px-6 py-3">User Email</th>
                    <th class="px-6 py-3">Service</th>
                    <th class="px-6 py-3">Server</th>
                    <th class="px-6 py-3">Number</th>
                    <th class="px-6 py-3">Country ID</th>
                    <th class="px-6 py-3">USD Price</th>
                    <th class="px-6 py-3">Markup</th>
                    <th class="px-6 py-3">Naira Charged</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Code</th>
                    <th class="px-6 py-3">Date</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse ($verifications as $verification)
                    <tr>
                        <td class="px-6 py-4">{{ $verification->user->email ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ strtoupper($verification->name ?? $verification->service) }}</td>
                        <td class="px-6 py-4">{{ $verification->server }}</td>
                        <td class="px-6 py-4">{{ $verification->number }}</td>
                        <td class="px-6 py-4">{{ $verification->country_id }}</td>
                        <td class="px-6 py-4">${{ $verification->price }}</td>
                        <td class="px-6 py-4">{{ $verification->markup }}%</td>
                        <td class="px-6 py-4">₦{{ number_format($verification->naira_amount, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                {{ $verification->status === 'done' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst($verification->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">{{ $verification->code ?? '—' }}</td>
                        <td class="px-6 py-4">{{ $verification->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-6 py-4 text-center text-gray-500">No verifications found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $verifications->appends(request()->query())->links('pagination::tailwind') }}
    </div>
@endsection

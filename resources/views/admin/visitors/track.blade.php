@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50 p-8">
    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Visitor Insights</h1>
            <p class="text-gray-500 mt-2">Daily stats and full page visit history</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-600 to-blue-500 text-white p-6 rounded-2xl shadow-lg">
                <p class="text-sm font-light">Today's Visits</p>
                <p class="text-3xl font-bold mt-2">{{ $stats['today'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow">
                <p class="text-sm text-gray-500">This Week</p>
                <p class="text-3xl font-bold mt-2 text-gray-900">{{ $stats['week'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow">
                <p class="text-sm text-gray-500">This Month</p>
                <p class="text-3xl font-bold mt-2 text-gray-900">{{ $stats['month'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow">
                <p class="text-sm text-gray-500">This Year</p>
                <p class="text-3xl font-bold mt-2 text-gray-900">{{ $stats['year'] }}</p>
            </div>
        </div>

        <!-- Visitor Sessions Table -->
        <div class="bg-white rounded-2xl shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-semibold">Page Visit History</h3>
                <input type="search" placeholder="Search..." class="pl-10 pr-4 py-2 border rounded-lg text-sm focus:ring-blue-500">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 font-medium text-gray-500">Visitor</th>
                            <th class="px-6 py-3 font-medium text-gray-500">Page Visited</th>
                            <th class="px-6 py-3 font-medium text-gray-500">Date & Time</th>
                            <th class="px-6 py-3 font-medium text-gray-500">Returning?</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($sessions as $session)
                        <tr>
                            <td class="px-6 py-4">
                                @if($session->user)
                                    <div class="font-medium text-gray-900">{{ $session->user->name }}</div>
                                    <div class="text-gray-500 text-xs">{{ $session->user->email }}</div>
                                @else
                                    <div class="text-gray-900">Guest</div>
                                    <div class="text-gray-500 text-xs">{{ $session->ip_address }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-blue-600">
                                {{ $session->url }}
                            </td>
                            <td class="px-6 py-4">
                                <div>{{ \Carbon\Carbon::parse($session->visited_at)->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($session->visited_at)->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $isReturning = \App\Models\VisitorSession::where('ip_address', $session->ip_address)->count() > 1;
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $isReturning ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $isReturning ? 'Yes' : 'No' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-100">
                {{ $sessions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

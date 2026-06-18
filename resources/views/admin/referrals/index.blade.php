@extends('layouts.admin')

@section('content')
<div class=" mx-auto px-2 py-6">
    <h1 class="text-2xl font-bold mb-6">Referral Signups</h1>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full text-sm table-auto">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">User</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Email</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Referred By</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($referredUsers as $user)
                    <tr class="border-b">
                        <td class="px-4 py-3">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            @if($user->referrer)
                                {{ $user->referrer->name }} ({{ $user->referrer->email }})
                            @else
                                <span class="text-gray-400 italic">Unknown</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $user->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">No referrals found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4">
            {{ $referredUsers->links() }}
        </div>
    </div>
</div>
@endsection

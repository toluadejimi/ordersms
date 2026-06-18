@extends('layouts.admin')

@section('content')
<div class=" mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4">🔧 Update Manual Funding Account</h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.manual.account.store') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium">Bank Name</label>
            <input type="text" name="bank_name" class="w-full border px-3 py-2 rounded" value="{{ $account->bank_name ?? '' }}">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium">Account Name</label>
            <input type="text" name="account_name" class="w-full border px-3 py-2 rounded" value="{{ $account->account_name ?? '' }}">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium">Account Number</label>
            <input type="text" name="account_number" class="w-full border px-3 py-2 rounded" value="{{ $account->account_number ?? '' }}">
        </div>

        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Save</button>
    </form>
</div>
@endsection

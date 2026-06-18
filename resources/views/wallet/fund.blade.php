@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-10">
    <h1 class="text-2xl font-bold mb-6">Fund Your Wallet</h1>

    @if (session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-3 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="#" onsubmit="return false;" x-data="{ method: '' }">
        @csrf
        <div class="mb-4">
            <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount (₦)</label>
            <input type="number" name="amount" id="amount" min="100" required
                   class="w-full border border-gray-300 rounded px-4 py-2 focus:ring focus:ring-indigo-400" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <button @click="method = 'manual'; $el.form.action=''; $el.form.submit()"
                    class="bg-gray-700 text-white py-2 rounded hover:bg-gray-800">Manual</button>
            <button @click="method = 'paystack'; $el.form.action='{{ route('wallet.paystack') }}'; $el.form.submit()"
                    class="bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700">Paystack</button>
            <button @click="method = 'flutterwave'; $el.form.action='{{ route('wallet.flutterwave') }}'; $el.form.submit()"
                    class="bg-pink-600 text-white py-2 rounded hover:bg-pink-700">Flutterwave</button>
        </div>
    </form>
</div>
@endsection

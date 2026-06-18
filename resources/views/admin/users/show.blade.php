@extends('layouts.admin')

@section('content')
<div class=" ">
    <!-- Success/Error Messages -->
    @if (session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-600 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl">
        {{ session('error') }}
    </div>
    @endif

    <!-- Profile Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-blue-600 rounded-2xl p-6 sm:p-8 shadow-lg text-white">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between space-y-4 sm:space-y-0">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white/10 rounded-full flex items-center justify-center">
                    <span class="text-xl sm:text-2xl font-bold">{{ substr($user->name, 0, 1) }}</span>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold">{{ $user->name }}</h1>
                    <p class="opacity-90 text-sm sm:text-base">{{ $user->email }}</p>
                </div>
            </div>
            <div class="text-left sm:text-right">
                <p class="text-sm opacity-90">Wallet Balance</p>
                <p class="text-2xl sm:text-3xl font-bold">₦{{ number_format($user->wallet, 2) }}</p>
            </div>
        </div>
    </div>
<br>
    <!-- Action Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Wallet Management -->
        <div class="bg-white p-5 sm:p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold mb-4">Wallet Management</h3>
            <form method="POST" action="{{ route('admin.users.fund', $user->id) }}" class="space-y-4">
                @csrf
                <input type="number" name="amount" placeholder="Amount"
                       class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                    Fund Wallet
                </button>
            </form>

            <hr class="my-6 border-gray-100">

            <form method="POST" action="{{ route('admin.users.debit', $user->id) }}">
                @csrf
                <input type="number" name="amount" placeholder="Amount"
                       class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 mb-4">
                <button type="submit"
                        class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition">
                    Debit Wallet
                </button>
            </form>
        </div>

        <!-- User Metadata -->
        <div class="bg-white p-5 sm:p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold mb-4">Account Details</h3>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm text-gray-500">Registration Date</dt>
                    <dd class="font-medium">{{ $user->created_at ? $user->created_at->format('d M Y') : 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Last Login</dt>
                    <dd class="font-medium">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</dd>
                </div>
                <div>
    <dt class="text-sm text-gray-500">Account Status</dt>
    <dd class="font-medium {{ $user->banned ? 'text-red-600' : 'text-emerald-600' }}">
        {{ $user->banned ? 'Banned' : 'Active' }}
    </dd>
</div>

            </dl>

            <hr class="my-6 border-gray-100">
<form method="POST" action="{{ route('admin.users.toggleBan', $user->id) }}">
    @csrf
    <button type="submit"
        class="w-full {{ $user->banned ? 'bg-green-600' : 'bg-red-600' }} text-white px-6 py-2 rounded-lg transition mt-4">
        {{ $user->banned ? 'Unban User' : 'Ban User' }}
    </button>
</form>
<br>


            @if(Auth::guard('admin')->check())
            <form action="{{ route('admin.users.impersonate', $user->id) }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full bg-amber-500 hover:bg-amber-600 text-white px-6 py-2 rounded-lg transition flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                    Login as User
                </button>
            </form>
            @endif
        </div>

        <!-- Quick Stats -->
        <div class="bg-white p-5 sm:p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold mb-4">Activity Summary</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-600">Total Transactions</span>
                    <span class="font-semibold">{{ $user->transactions->count() }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-600">Pending Verifications</span>
                    <span class="font-semibold text-amber-600">{{ $user->verifications->where('status', 'pending')->count() }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-600">Completed Verifications</span>
                    <span class="font-semibold text-emerald-600">{{ $user->verifications->where('status', 'done')->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Transactions -->
        <div class="bg-white p-5 sm:p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold">Recent Transactions</h3>
                <a href="{{ route('admin.users.show', $user->id) }}?tab=transactions" class="text-blue-600 text-sm hover:underline">View all</a>
            </div>
            <div class="space-y-4">
                @forelse ($user->transactions->take(5) as $txn)
                    <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition">
                        <div>
                            <p class="font-medium">{{ $txn->type }} <span class="text-gray-500 text-sm">via {{ $txn->method ?? 'system' }}</span></p>
                            <p class="text-sm text-gray-500">{{ $txn->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="{{ $txn->type === 'debit' ? 'text-red-600' : 'text-emerald-600' }} font-semibold">
                            ₦{{ number_format($txn->amount, 2) }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No transactions found</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Verifications -->
        <div class="bg-white p-5 sm:p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold">Recent Verifications</h3>
                <a href="{{ route('admin.users.show', $user->id) }}?tab=verifications" class="text-blue-600 text-sm hover:underline">View all</a>
            </div>
            <div class="space-y-4">
                @forelse ($user->verifications->take(5) as $v)
                    <div class="p-3 rounded-lg hover:bg-gray-50 transition">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-medium text-gray-800">
                                    {{ $v->name ?? $v->service }} 
                                    <span class="text-gray-500 text-sm">({{ ucfirst($v->server) }})</span>
                                </p>
                                <p class="text-sm text-gray-500">+{{ $v->number }} — {{ $v->status }}</p>
                                <p class="text-xs text-gray-400">{{ $v->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-700">₦{{ number_format($v->naira_amount ?? $v->price, 2) }}</p>
                                @if($v->code)
                                    <p class="text-xs text-emerald-600 mt-1">Code: {{ $v->code }}</p>
                                @else
                                    <p class="text-xs text-red-500 mt-1">No code</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No verifications found</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

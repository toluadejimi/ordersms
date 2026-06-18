@extends('layouts.admin')

@section('content')
<div class="space-y-4">
    @if(session('success'))
        <div class="bg-green-50 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-xl font-bold text-gray-900">Log Viewer</h1>
        <div class="flex items-center gap-3">
            @if($selected)
                <a href="{{ route('admin.logs', ['file' => $selected]) }}"
                   class="text-sm text-blue-600 hover:text-blue-800">↻ Refresh</a>
                <form method="POST" action="{{ route('admin.logs.clear') }}"
                      onsubmit="return confirm('Clear all entries in {{ $selected }}?')">
                    @csrf
                    <input type="hidden" name="file" value="{{ $selected }}">
                    <button type="submit"
                            class="px-3 py-1.5 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">
                        Clear Log
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid lg:grid-cols-4 gap-4">
        <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                <h2 class="text-xs font-bold text-gray-500 uppercase tracking-widest">Log Files</h2>
            </div>
            <div class="max-h-[70vh] overflow-y-auto divide-y divide-gray-100">
                @forelse($files as $file)
                    <a href="{{ route('admin.logs', array_filter(['file' => $file, 'q' => $search ?: null])) }}"
                       class="block px-4 py-3 text-sm hover:bg-blue-50 {{ $selected === $file ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700' }}">
                        {{ $file }}
                        @if(str_starts_with($file, 'sprintpay'))
                            <span class="ml-1 text-xs text-emerald-600">SprintPay</span>
                        @endif
                    </a>
                @empty
                    <p class="px-4 py-6 text-sm text-gray-500">No log files found in storage/logs.</p>
                @endforelse
            </div>
        </div>

        <div class="lg:col-span-3 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center gap-3">
                <h2 class="text-sm font-semibold text-gray-800 flex-1">
                    {{ $selected ?: 'Select a log file' }}
                </h2>
                <form method="GET" action="{{ route('admin.logs') }}" class="flex gap-2">
                    <input type="hidden" name="file" value="{{ $selected }}">
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search logs..."
                           class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm w-full sm:w-56">
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">Search</button>
                </form>
            </div>

            <div class="max-h-[70vh] overflow-auto p-4 bg-gray-900 text-gray-100 font-mono text-xs leading-relaxed">
                @forelse($lines as $line)
                    @php
                        $class = match (true) {
                            str_contains($line, '.ERROR:') || str_contains($line, 'Credit failed') => 'text-red-400',
                            str_contains($line, '.WARNING:') || str_contains($line, 'Credit blocked') => 'text-amber-300',
                            str_contains($line, '.INFO:') && str_contains($line, 'Wallet credited') => 'text-emerald-400',
                            str_contains($line, '[webhook]') => 'text-sky-300',
                            str_contains($line, '[verify]') => 'text-violet-300',
                            default => 'text-gray-300',
                        };
                    @endphp
                    <div class="{{ $class }} whitespace-pre-wrap break-all mb-1">{{ $line }}</div>
                @empty
                    <p class="text-gray-400">
                        @if($selected)
                            No log lines to show{{ $search ? ' for this search' : '' }}.
                        @else
                            Choose a log file from the left.
                        @endif
                    </p>
                @endforelse
            </div>
            @if($lines->count() >= 500)
                <p class="px-4 py-2 text-xs text-gray-500 border-t border-gray-200">Showing latest 500 matching lines.</p>
            @endif
        </div>
    </div>
</div>
@endsection

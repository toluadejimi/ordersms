@extends('layouts.admin')
@section('content')
<div class="mx-auto px-2 py-2">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">User Support Inbox</h1>
                        <p class="text-orange-100">Manage customer support conversations</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Stats Display -->
                    <div class="bg-white bg-opacity-20 rounded-full px-3 py-1">
                        <span class="text-sm font-medium">{{ $filteredCount ?? $users->count() }} of {{ $totalConversations ?? $users->count() }} conversations</span>
                    </div>
                    @if(isset($users) && $users->sum('unread_count') > 0)
                        <div class="bg-red-500 rounded-full px-3 py-1">
                            <span class="text-sm font-medium">{{ $users->sum('unread_count') }} unread</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-gray-50 border-b border-gray-200 p-6">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search Input -->
                    <div class="lg:col-span-2">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Search by username or email..." 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        </div>
                    </div>

                    <!-- Sort Dropdown -->
                    <div>
                        <select name="sort" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            <option value="">Sort by...</option>
                            <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Latest Message</option>
                            <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest Message</option>
                            <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name A-Z</option>
                            <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name Z-A</option>
                            <option value="unread" {{ request('sort') === 'unread' ? 'selected' : '' }}>Most Unread</option>
                        </select>
                    </div>

                    <!-- Search Button -->
                    <div class="flex space-x-2">
                        <button type="submit" 
                                class="flex-1 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <span>Search</span>
                        </button>
                        @if(request()->hasAny(['search', 'sort', 'date_from', 'date_to']))
                            <a href="{{ route('admin.support.inbox') }}" 
                               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Date Range Filters -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                        <input type="date" 
                               name="date_from" 
                               value="{{ request('date_from') }}"
                               class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                        <input type="date" 
                               name="date_to" 
                               value="{{ request('date_to') }}"
                               class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    </div>
                </div>
            </form>
        </div>

        <!-- Content -->
        <div class="p-6">
            @if ($users->count())
                <div class="space-y-3">
                    @foreach ($users as $user)
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-all duration-200 hover:border-orange-200 {{ isset($user->unread_count) && $user->unread_count > 0 ? 'border-l-4 border-l-orange-500' : '' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="relative">
                                        <div class="w-10 h-10 bg-gradient-to-r from-orange-400 to-orange-500 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        @if(isset($user->unread_count) && $user->unread_count > 0)
                                            <div class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center">
                                                <span class="text-xs text-white font-bold">{{ $user->unread_count > 9 ? '9+' : $user->unread_count }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-800">
                                            {{ $user->username ?? $user->email }}
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            @if($user->username && $user->email)
                                                {{ $user->email }}
                                            @else
                                                User ID: {{ $user->id }}
                                            @endif
                                        </p>
                                        @if(isset($user->last_message) && $user->last_message)
                                            <p class="text-xs text-gray-400 mt-1 truncate max-w-xs">
                                                Last: {{ Str::limit($user->last_message->message ?: 'Image attachment', 50) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-4">
                                    <div class="text-right">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-2 h-2 {{ isset($user->unread_count) && $user->unread_count > 0 ? 'bg-red-400' : 'bg-green-400' }} rounded-full"></div>
                                            <span class="text-xs text-gray-500">{{ isset($user->unread_count) && $user->unread_count > 0 ? 'Unread' : 'Active' }}</span>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-1">
                                            Last message: {{ isset($user->last_message) && $user->last_message ? $user->last_message->created_at->diffForHumans() : $user->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        @if(isset($user->unread_count) && $user->unread_count > 0)
                                            <button onclick="markAsRead({{ $user->id }})" 
                                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors duration-200">
                                                Mark Read
                                            </button>
                                        @endif
                                        
                                        <a href="{{ route('admin.chat.view', $user->id) }}" 
                                           class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 transform hover:scale-105 flex items-center space-x-2 shadow-md">
                                            <span>View Chat</span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Results Summary -->
                <div class="mt-8 flex justify-between items-center">
                    <div class="bg-gray-50 rounded-lg px-4 py-2">
                        <p class="text-sm text-gray-600">
                            Showing {{ $users->count() }} of {{ $totalConversations ?? $users->count() }} conversations
                            @if(request('search'))
                                for "{{ request('search') }}"
                            @endif
                        </p>
                    </div>
                    
                    @if(request()->hasAny(['search', 'sort', 'date_from', 'date_to']))
                        <div class="bg-blue-50 rounded-lg px-4 py-2">
                            <p class="text-sm text-blue-600">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"></path>
                                </svg>
                                Filters applied
                            </p>
                        </div>
                    @endif
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    @if(request()->hasAny(['search', 'sort', 'date_from', 'date_to']))
                        <!-- No Results State -->
                        <div class="w-24 h-24 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-12 h-12 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Results Found</h3>
                        <p class="text-gray-500 mb-6">No conversations match your search criteria. Try adjusting your filters.</p>
                        <a href="{{ route('admin.chat.inbox') }}" 
                           class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                            Clear Filters
                        </a>
                    @else
                        <!-- No Data State -->
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Support Messages Yet</h3>
                        <p class="text-gray-500 mb-6">When users send support messages, they'll appear here for you to manage.</p>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 max-w-md mx-auto">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm font-medium text-blue-800">Pro Tip</span>
                            </div>
                            <p class="text-sm text-blue-700 mt-2">Users can access the support chat from their dashboard to start conversations with your team.</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Mark conversation as read
    function markAsRead(userId) {
        fetch(`{{ route('admin.chat.mark-read', ':id') }}`.replace(':id', userId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error marking as read:', error);
        });
    }

    // Delete entire conversation
    function deleteConversation(userId) {
        if (confirm('Are you sure you want to delete this entire conversation? This will remove all messages and cannot be undone.')) {
            fetch(`{{ route('admin.chat.delete-conversation', ':id') }}`.replace(':id', userId), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error deleting conversation');
                }
            })
            .catch(error => {
                console.error('Error deleting conversation:', error);
                alert('Error deleting conversation');
            });
        }
    }

    // Add interactive enhancements
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover effects to conversation items
        const conversationItems = document.querySelectorAll('.bg-gray-50');
        
        conversationItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
        
        // Auto-submit form on sort change
        const sortSelect = document.querySelector('select[name="sort"]');
        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                this.form.submit();
            });
        }

        // Auto-refresh the inbox every 30 seconds (only if no search is active)
        if (!{{ request()->hasAny(['search', 'sort', 'date_from', 'date_to']) ? 'true' : 'false' }}) {
            setInterval(function() {
                if (document.visibilityState === 'visible') {
                    window.location.reload();
                }
            }, 30000);
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+F to focus search
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                document.querySelector('input[name="search"]').focus();
            }
        });
        
        // Real-time search suggestion (optional)
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                
                if (query.length >= 2) {
                    searchTimeout = setTimeout(() => {
                        // Auto-submit after 1 second of no typing
                        // this.form.submit();
                    }, 1000);
                }
            });
        }
    });
</script>
@endsection
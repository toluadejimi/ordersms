@extends('layouts.admin')
@section('content')
<div class=" mx-auto  py-2">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">Admin Chat</h1>
                        <p class="text-orange-100">Chatting with {{ $user->username ?? $user->email }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Back to Inbox -->
                    <a href="{{ route('admin.chat.inbox') }}" 
                       class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-3 py-1 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span>Back to Inbox</span>
                    </a>
                    
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-sm text-orange-100">Online</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Messages Bar -->
        <div class="bg-gray-50 border-b border-gray-200 p-4">
            <form method="GET" class="flex items-center space-x-4">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" 
                           name="message_search" 
                           value="{{ request('message_search') }}"
                           placeholder="Search messages in this conversation..." 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                </div>
                
                <button type="submit" 
                        class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>Search</span>
                </button>
                
                @if(request('message_search'))
                    <a href="{{ route('admin.chat.view', $user->id) }}" 
                       class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </a>
                @endif
            </form>
            
            @if(request('message_search'))
                <div class="mt-2 text-sm text-gray-600">
                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                    </svg>
                    Searching for: "<strong>{{ request('message_search') }}</strong>" 
                    <span class="text-gray-500">({{ $messages->count() }} {{ $messages->count() === 1 ? 'result' : 'results' }})</span>
                </div>
            @endif
        </div>

        <!-- Success Message -->
        @if (session('reply_sent'))
            <div id="success-message" class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 m-4 rounded-r-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ session('reply_sent') }}
                </div>
            </div>
        @endif

        <!-- Chat Messages -->
        <div id="chat-box" class="h-[32rem] overflow-y-auto p-6 bg-gray-50 border-b border-gray-200">
            @forelse ($messages as $msg)
                <div class="mb-6 flex {{ $msg->sender_type === \App\Models\Admin::class ? 'justify-end' : 'justify-start' }}" 
                     data-message-id="{{ $msg->id }}">
                    <div class="max-w-lg">
                        @if ($msg->sender_type === \App\Models\Admin::class)
                            <!-- Admin Message -->
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4 rounded-lg rounded-br-none shadow-md {{ request('message_search') && stripos($msg->message, request('message_search')) !== false ? 'ring-2 ring-yellow-400' : '' }} group">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-2">
                                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <span class="text-xs font-medium text-blue-100">Admin</span>
                                    </div>
                                    <!-- Delete Button for Admin Messages -->
                                    <button onclick="deleteMessage({{ $msg->id }})" 
                                            class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-red-500 hover:bg-red-600 text-white p-1 rounded text-xs"
                                            title="Delete message">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                                @if ($msg->image_path)
                                    <img src="{{ asset('storage/app/public/' . $msg->image_path) }}" class="w-56 h-auto mb-3 rounded-lg border-2 border-blue-300">
                                @endif
                                @if ($msg->message)
                                    <p class="text-sm leading-relaxed">
                                        @if(request('message_search'))
                                            {!! str_ireplace(request('message_search'), '<mark class="bg-yellow-200 text-gray-800 px-1 rounded">' . request('message_search') . '</mark>', e($msg->message)) !!}
                                        @else
                                            {{ $msg->message }}
                                        @endif
                                    </p>
                                @endif
                                <p class="text-xs text-blue-100 mt-2 text-right">{{ $msg->created_at->diffForHumans() }}</p>
                            </div>
                        @else
                            <!-- User Message -->
                            <div class="bg-white border-2 border-orange-200 p-4 rounded-lg rounded-bl-none shadow-md {{ request('message_search') && stripos($msg->message, request('message_search')) !== false ? 'ring-2 ring-yellow-400' : '' }} group">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 bg-orange-500 rounded-full flex items-center justify-center mr-2">
                                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <span class="text-xs font-medium text-gray-600">{{ $user->username ?? 'User' }}</span>
                                    </div>
                                    <!-- Delete Button for User Messages -->
                                    <button onclick="deleteMessage({{ $msg->id }})" 
                                            class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-red-500 hover:bg-red-600 text-white p-1 rounded text-xs"
                                            title="Delete message">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                                @if ($msg->image_path)
                                    <img src="{{ asset('storage/app/public/' . $msg->image_path) }}" class="w-56 h-auto mb-3 rounded-lg border border-gray-200">
                                @endif
                                @if ($msg->message)
                                    <p class="text-sm text-gray-700 leading-relaxed">
                                        @if(request('message_search'))
                                            {!! str_ireplace(request('message_search'), '<mark class="bg-yellow-200 text-gray-800 px-1 rounded">' . request('message_search') . '</mark>', e($msg->message)) !!}
                                        @else
                                            {{ $msg->message }}
                                        @endif
                                    </p>
                                @endif
                                <p class="text-xs text-gray-500 mt-2">{{ $msg->created_at->diffForHumans() }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <!-- No Messages State -->
                <div class="text-center py-12">
                    @if(request('message_search'))
                        <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Messages Found</h3>
                        <p class="text-gray-500 mb-4">No messages contain "{{ request('message_search') }}"</p>
                        <a href="{{ route('admin.chat.view', $user->id) }}" 
                           class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                            Show All Messages
                        </a>
                    @else
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Messages Yet</h3>
                        <p class="text-gray-500">Start the conversation by sending a message below.</p>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Message Input -->
        <div class="bg-white p-6 border-t border-gray-200">
            <form method="POST" action="{{ route('admin.chat.reply', $user->id) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="flex items-start space-x-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <textarea 
                            name="message" 
                            rows="3" 
                            placeholder="Type your admin reply here..." 
                            class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            required
                        ></textarea>
                    </div>
                </div>
                
                <div class="flex items-center justify-between pl-14">
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center cursor-pointer text-sm text-gray-600 hover:text-gray-800">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span id="file-label">Attach Image</span>
                            <input type="file" name="image" class="hidden" accept="image/*">
                        </label>
                        
                        <div class="text-xs text-gray-500">
                            <kbd class="px-2 py-1 bg-gray-100 rounded">Ctrl</kbd> + <kbd class="px-2 py-1 bg-gray-100 rounded">Enter</kbd> to send
                        </div>
                    </div>
                    
                    <button 
                        type="submit" 
                        class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 flex items-center space-x-2 shadow-md"
                    >
                        <span>Send Reply</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Delete individual message
    function deleteMessage(messageId) {
        if (confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
            fetch(`{{ route('admin.chat.delete-message', ':id') }}`.replace(':id', messageId), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the message element
                    const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
                    if (messageElement) {
                        messageElement.style.transition = 'opacity 0.3s ease-out';
                        messageElement.style.opacity = '0';
                        setTimeout(() => {
                            messageElement.remove();
                        }, 300);
                    }
                    
                    // Show success message
                    showNotification('Message deleted successfully', 'success');
                } else {
                    showNotification(data.message || 'Error deleting message', 'error');
                }
            })
            .catch(error => {
                console.error('Error deleting message:', error);
                showNotification('Error deleting message', 'error');
            });
        }
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
                    // Redirect to inbox
                    window.location.href = '{{ route("admin.chat.inbox") }}';
                } else {
                    showNotification(data.message || 'Error deleting conversation', 'error');
                }
            })
            .catch(error => {
                console.error('Error deleting conversation:', error);
                showNotification('Error deleting conversation', 'error');
            });
        }
    }

    // Show notification
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transition-all duration-300 ${
            type === 'success' 
                ? 'bg-green-500 text-white' 
                : 'bg-red-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    function fetchMessages() {
        // Preserve search parameters when polling
        const searchParams = new URLSearchParams(window.location.search);
        const pollingUrl = "{{ route('admin.chat.view', $user->id) }}?" + searchParams.toString() + (searchParams.toString() ? '&' : '') + 'polling=true';
        
        fetch(pollingUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            const newMessages = tempDiv.querySelector('#chat-box').innerHTML;
            const currentChatBox = document.querySelector('#chat-box');
            
            // Only update if content has changed and we're not searching
            if (currentChatBox.innerHTML !== newMessages && !{{ request('message_search') ? 'true' : 'false' }}) {
                currentChatBox.innerHTML = newMessages;
                // Smooth scroll to bottom
                currentChatBox.scrollTo({
                    top: currentChatBox.scrollHeight,
                    behavior: 'smooth'
                });
            }
        })
        .catch(error => {
            console.error('Error fetching messages:', error);
        });
    }

    // Auto-hide success message after 5 seconds
    const successMessage = document.getElementById('success-message');
    if (successMessage) {
        setTimeout(() => {
            successMessage.style.transition = 'opacity 0.5s ease-out';
            successMessage.style.opacity = '0';
            setTimeout(() => {
                successMessage.remove();
            }, 500);
        }, 5000);
    }

    // Auto-resize textarea
    const textarea = document.querySelector('textarea[name="message"]');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 150) + 'px';
        });

        // Ctrl+Enter to send
        textarea.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                this.form.submit();
            }
        });
    }

    // File input feedback
    const fileInput = document.querySelector('input[type="file"]');
    const fileLabel = document.getElementById('file-label');
    if (fileInput && fileLabel) {
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileLabel.textContent = this.files[0].name;
                fileLabel.classList.add('text-blue-600', 'font-medium');
            } else {
                fileLabel.textContent = 'Attach Image';
                fileLabel.classList.remove('text-blue-600', 'font-medium');
            }
        });
    }

    // Search functionality
    const searchInput = document.querySelector('input[name="message_search"]');
    if (searchInput) {
        // Focus on Ctrl+F
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                searchInput.focus();
            }
        });

        // Auto-scroll to first highlighted message
        if ({{ request('message_search') ? 'true' : 'false' }}) {
            const firstHighlighted = document.querySelector('.ring-yellow-400');
            if (firstHighlighted) {
                setTimeout(() => {
                    firstHighlighted.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                }, 500);
            }
        }
    }

    // Initialize
    setInterval(fetchMessages, 5000);
    window.onload = function() {
        if (!{{ request('message_search') ? 'true' : 'false' }}) {
            fetchMessages();
        }
        // Scroll to bottom on page load (unless searching)
        const chatBox = document.getElementById('chat-box');
        if (!{{ request('message_search') ? 'true' : 'false' }}) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    };
</script>
@endsection
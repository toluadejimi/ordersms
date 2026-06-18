<div wire:poll.5s>
    <div class="space-y-3 max-h-[400px] overflow-y-auto p-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded mb-4">
        @foreach ($messages as $msg)
            <div class="flex {{ $msg->sender_id === auth()->id() && $msg->sender_type === get_class(auth()->user()) ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-xs p-3 rounded-lg text-sm {{ $msg->sender_id === auth()->id() && $msg->sender_type === get_class(auth()->user()) ? 'bg-orange-100 dark:bg-orange-900 text-right' : 'bg-gray-200 dark:bg-gray-700 text-left' }}">
                    @if ($msg->image_path)
                        <img src="{{ asset('storage/' . $msg->image_path) }}" class="w-40 h-auto mb-2 rounded">
                    @endif
                    @if ($msg->message)
                        <p>{{ $msg->message }}</p>
                    @endif
                    <p class="text-xs text-gray-500 mt-1">{{ $msg->created_at->diffForHumans() }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <form wire:submit.prevent="sendMessage" class="space-y-3">
        <textarea wire:model.defer="message" rows="3" class="w-full p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded" placeholder="Type a message..."></textarea>
        <input type="file" wire:model="image" class="text-sm text-gray-500 dark:text-gray-300">
        <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded">Send</button>
    </form>
</div>

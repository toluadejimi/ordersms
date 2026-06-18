<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatBox extends Component
{
    use WithFileUploads;

    public $message;
    public $image;
    public $messages;

    public function mount()
    {
        $this->loadMessages();
    }

   

public function sendMessage()
{
    if (!$this->message && !$this->image) {
        Log::debug('Message and image both empty — skipping save.');
        return;
    }

    $user = auth()->user();
    Log::debug('User attempting to send message', [
        'user_id' => $user->id,
        'user_type' => get_class($user),
        'message' => $this->message,
        'has_image' => $this->image ? 'yes' : 'no',
    ]);

    $path = $this->image ? $this->image->store('chat_images', 'public') : null;

    $msg = Message::create([
        'sender_id'    => $user->id,
        'sender_type'  => get_class($user),
        'receiver_id'  => 1,
        'message'      => $this->message,
        'image_path'   => $path,
    ]);

    Log::debug('Message created', ['id' => $msg->id]);

    $this->reset(['message', 'image']);
    $this->loadMessages();
}

    public function loadMessages()
    {
        $user = auth()->user();

        $this->messages = Message::where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->where('sender_type', get_class($user));
            })
            ->orWhere(function ($q) use ($user) {
                $q->where('receiver_id', $user->id)
                  ->where('sender_type', \App\Models\Admin::class);
            })
            ->orderBy('created_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.chat-box');
    }
}

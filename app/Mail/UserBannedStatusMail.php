<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UserBannedStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $status;

    public function __construct(User $user, $status)
    {
        $this->user = $user;
        $this->status = $status; // 'banned' or 'unbanned'
    }

    public function build()
    {
        return $this->subject("Your Account Has Been {$this->status}")
                    ->view('emails.user-ban-status');
    }
}

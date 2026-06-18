<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminCreditedWalletMail extends Mailable
{
    use Queueable, SerializesModels;

    public $amount;
    public $balance;
    public $user;

    public function __construct($amount, $balance, $user)
    {
        $this->amount = $amount;
        $this->balance = $balance;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Your Wallet Has Been Credited by Admin')
                    ->view('emails.admin_wallet_credit');
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WalletFundedAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public $amount;
    public $method;
    public $user;

    public function __construct($amount, $method, $user)
    {
        $this->amount = $amount;
        $this->method = $method;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('User Wallet Funded')
                    ->view('emails.wallet_funded_admin');
    }
}

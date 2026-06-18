<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WalletFundedUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public $amount;
    public $method;

    public function __construct($amount, $method)
    {
        $this->amount = $amount;
        $this->method = $method;
    }

    public function build()
    {
        return $this->subject('Wallet Funded Successfully')
                    ->view('emails.wallet_funded_user');
    }
}

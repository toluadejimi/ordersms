<?php

// app/Mail/ManualFundingSubmittedMail.php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ManualFundingSubmittedMail extends Mailable
{
    use SerializesModels;

    public $user;
    public $amount;
    public $note;

    public function __construct($user, $amount, $note = null)
    {
        $this->user = $user;
        $this->amount = $amount;
        $this->note = $note;
    }

    public function build()
    {
        return $this->subject('New Manual Funding Request')
                    ->view('emails.manual_funding_submitted');
    }
}

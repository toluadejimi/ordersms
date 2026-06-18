<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ManualFundingStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $status;
    public $amount;
    public $balance;
    public $note;

    public function __construct($status, $amount, $balance, $note = null)
    {
        $this->status = $status;
        $this->amount = (float) $amount;
        $this->balance = (float) $balance;
        $this->note = $note;
    }

    public function build()
    {
        return $this->subject('Manual Funding ' . ucfirst($this->status))
            ->view('emails.manual_funding_status')
            ->with([
                'status' => $this->status,
                'amount' => $this->amount,
                'balance' => $this->balance,
                'note' => $this->note,
            ]);
    }
}

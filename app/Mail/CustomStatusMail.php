<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomStatusMail extends Mailable
{
    use Queueable, SerializesModels;
    public $fullName;
    public $status;
    public $memberNumber;

    public function __construct($fullName, $status, $memberNumber = null)
    {
        $this->fullName = $fullName;
        $this->status = $status;
        $this->memberNumber = $memberNumber;
    }

    public function build()
    {
        $subject = match ($this->status) {
            'Approve' => 'Approval Confirmation',
            default => 'Application Status Update',
        };

        return $this->subject($subject)->view('emails.custom_status');
    }
}

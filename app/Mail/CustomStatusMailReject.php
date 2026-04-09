<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomStatusMailReject extends Mailable
{
    use Queueable, SerializesModels;

    public $fullName;
    public $status;

    public function __construct($fullName, $status)
    {
        $this->fullName = $fullName;
        $this->status = $status;
    }

    public function build()
    {
        $subject = match ($this->status) {
            'Approve' => 'Approval Confirmation',
            default => 'Application Status Update',
        };

        return $this->subject($subject)->view('emails.registrationreject');
    }
}

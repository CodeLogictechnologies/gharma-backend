<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $full_name;

    public function __construct($full_name)
    {
        $this->full_name = $full_name;
    }

    public function build()
    {
        return $this->markdown('emails.registration.submitted')
            ->subject('Registration Form Submitted')
            ->with([
                'full_name' => $this->full_name,
            ]);
    }
}

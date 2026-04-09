<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;
    public $user, $otp;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $digit)
    {
        $this->user = $user;
        $this->otp = $digit;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.sendotp');
    }
}

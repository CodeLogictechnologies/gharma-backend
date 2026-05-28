<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject('Your Account Password')
            ->view('emails.user_password')
            ->with([
                'name' => $this->data['name'],
                'email' => $this->data['email'],
                'password' => $this->data['password'],
            ]);
    }
}

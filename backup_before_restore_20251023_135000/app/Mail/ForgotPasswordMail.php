<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tempPassword;

    /**
     * Create a new message instance.
     */
    public function __construct($mailData)
    {
        $this->tempPassword = $mailData['tempPassword'];
    }

    public function build()
    {
        return $this->view('email.forgottemplate')
                    ->with(['tempPassword' => $this->tempPassword]);
    }
}

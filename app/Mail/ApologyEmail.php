<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApologyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $email,
        public string $token,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🙏 Permohonan Maaf - Sistem Error Registrasi',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.apology',
            with: [
                'email' => $this->email,
                'token' => $this->token,
                'reregisterUrl' => route('checkout.reregister', ['token' => $this->token]),
            ],
        );
    }
}

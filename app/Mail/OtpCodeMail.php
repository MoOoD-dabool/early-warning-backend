<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly string $otpCode)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'رمز التحقق - تطبيق الإنذار المبكر',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp-code',
            with: ['otpCode' => $this->otpCode],
        );
    }
}
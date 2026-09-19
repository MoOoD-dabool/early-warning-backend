<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewReportNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Report $report)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'بلاغ جديد - تطبيق الإنذار المبكر',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-report-notification',
            with: ['report' => $this->report],
        );
    }
}
<?php

namespace App\Mail;

use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FeedbackReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Feedback $feedback,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thank You for Your Feedback, ' . $this->feedback->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.feedback-received',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

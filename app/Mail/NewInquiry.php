<?php

namespace App\Mail;

use App\Models\ContactInquiry;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;

class NewInquiry extends Mailable
{
    public function __construct(public ContactInquiry $inquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->inquiry->email, $this->inquiry->name)],
            subject: 'New website inquiry from ' . $this->inquiry->name,
        );
    }

    public function content(): Content
    {
        return new Content(text: 'mail.inquiry');
    }
}

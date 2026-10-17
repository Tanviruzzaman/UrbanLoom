<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{name: string, email: string, phone: ?string, order_number: ?string, subject: string, message: string}  $data
     */
    public function __construct(public array $data) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('store.name').' Website'),
            // Sent from our own address so SPF/DKIM stay valid; hitting Reply
            // in the inbox goes straight back to the customer.
            replyTo: [new Address($this->data['email'], $this->data['name'])],
            subject: '[Contact] '.$this->data['subject'],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-message');
    }
}

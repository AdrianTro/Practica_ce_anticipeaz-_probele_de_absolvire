<?php

namespace App\Mail;

use App\Models\ContactMessage;
use App\Models\ContactThread;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class ContactThreadCustomerReply extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactThread $thread,
        public ContactMessage $contactMessage
    ) {}

    public function envelope(): Envelope
    {
        $companyAddress = (string) config('mail.company_address');
        $companyName = (string) config('mail.from.name', 'ReclamDesign Modern');

        return new Envelope(
            bcc: [$companyAddress],
            replyTo: [new Address($companyAddress, $companyName)],
            subject: $this->thread->mailSubject(),
            using: [
                function (Email $email): void {
                    $headers = $email->getHeaders();

                    if ($headers->has('Message-ID')) {
                        $headers->remove('Message-ID');
                    }

                    $root = '<'.$this->thread->rootEmailMessageId().'>';

                    $headers->addIdHeader('Message-ID', $this->thread->companyEmailMessageId($this->contactMessage));
                    $headers->addTextHeader('In-Reply-To', $root);
                    $headers->addTextHeader('References', $root);
                    $headers->addTextHeader('X-ReclamDesign-Thread', $this->thread->thread_uuid);
                },
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact_customer_reply',
            with: [
                'thread' => $this->thread,
                'contactMessage' => $this->contactMessage,
                'customerUrl' => route('contacte.thread.show', $this->thread->public_token),
            ],
        );
    }
}

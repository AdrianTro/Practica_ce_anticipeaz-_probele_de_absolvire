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

class ContactThreadNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactThread $thread,
        public ContactMessage $contactMessage,
        public bool $isInitial = false
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->thread->email, $this->thread->fullName())],
            subject: $this->thread->mailSubject(),
            using: [
                function (Email $email): void {
                    $headers = $email->getHeaders();

                    if ($headers->has('Message-ID')) {
                        $headers->remove('Message-ID');
                    }

                    $messageId = $this->isInitial
                        ? $this->thread->rootEmailMessageId()
                        : $this->thread->customerEmailMessageId($this->contactMessage);

                    $headers->addIdHeader('Message-ID', $messageId);
                    $headers->addTextHeader('X-ReclamDesign-Thread', $this->thread->thread_uuid);

                    if (! $this->isInitial) {
                        $root = '<'.$this->thread->rootEmailMessageId().'>';
                        $headers->addTextHeader('In-Reply-To', $root);
                        $headers->addTextHeader('References', $root);
                    }
                },
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact_thread_notification',
            with: [
                'thread' => $this->thread,
                'contactMessage' => $this->contactMessage,
                'adminUrl' => route('admin.claims.show', $this->thread->thread_uuid),
            ],
        );
    }
}

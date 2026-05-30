<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class CompanyOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing([
            'items.product.images',
            'items.product.category',
            'items.product.subcategory',
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Comanda noua '.$this->order->order_uuid,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.company_order',
            with: ['order' => $this->order],
        );
    }

    public function attachments(): array
    {
        return $this->designAttachments();
    }

    private function designAttachments(): array
    {
        $attachments = [];
        $seen = [];

        foreach ($this->order->items as $itemIndex => $item) {
            foreach (($item->options['design_items'] ?? []) as $designIndex => $design) {
                $dataUri = is_array($design) ? ($design['image'] ?? null) : null;
                $parsed = $this->parseDataUri($dataUri);
                if (! $parsed) {
                    continue;
                }

                [$bytes, $mime, $extension] = $parsed;
                $hash = sha1($bytes);
                if (isset($seen[$hash])) {
                    continue;
                }
                $seen[$hash] = true;

                $side = is_array($design) ? ($design['side'] ?? 'design') : 'design';
                $safeSide = Str::slug((string) $side) ?: 'design';
                $name = $this->order->order_uuid.'-produs-'.($itemIndex + 1).'-design-'.($designIndex + 1).'-'.$safeSide.'.'.$extension;

                $attachments[] = Attachment::fromData(fn () => $bytes, $name)->withMime($mime);
            }

            foreach (($item->options['design_previews'] ?? []) as $side => $dataUri) {
                $parsed = $this->parseDataUri($dataUri);
                if (! $parsed) {
                    continue;
                }

                [$bytes, $mime, $extension] = $parsed;
                $hash = sha1($bytes);
                if (isset($seen[$hash])) {
                    continue;
                }
                $seen[$hash] = true;

                $safeSide = Str::slug((string) $side) ?: 'preview';
                $name = $this->order->order_uuid.'-produs-'.($itemIndex + 1).'-aplicat-'.$safeSide.'.'.$extension;

                $attachments[] = Attachment::fromData(fn () => $bytes, $name)->withMime($mime);
            }
        }

        return $attachments;
    }

    private function parseDataUri(mixed $dataUri): ?array
    {
        if (! is_string($dataUri) || ! preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,(.+)$/', $dataUri, $matches)) {
            return null;
        }

        $bytes = base64_decode($matches[2], true);
        if ($bytes === false) {
            return null;
        }

        $type = $matches[1] === 'jpg' ? 'jpeg' : $matches[1];
        $extension = $type === 'jpeg' ? 'jpg' : $type;

        return [$bytes, 'image/'.$type, $extension];
    }
}

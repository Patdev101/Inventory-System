<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PurchaseOrder $purchaseOrder,
        public string $emailSubject,
        public string $messageBody
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'purchase-orders.mail',
            with: [
                'purchaseOrder' => $this->purchaseOrder,
                'messageBody' => $this->messageBody,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $pdf = Pdf::loadView(
            'purchase-orders.pdf',
            ['purchaseOrder' => $this->purchaseOrder]
        )->setPaper('a4');

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                $this->purchaseOrder->po_number . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}

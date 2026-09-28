<?php

namespace App\Notifications;

use App\Models\StockMovementRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockMovementRequestSubmitted extends Notification
{
    use Queueable;

    public function __construct(public StockMovementRequest $stockMovementRequest)
    {
        $this->stockMovementRequest->loadMissing([
            'product',
            'location',
            'destinationLocation',
            'requestedBy',
        ]);
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (config('stockapprovals.email_enabled')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'stock_movement_request_id' => $this->stockMovementRequest->id,
            'type' => $this->stockMovementRequest->type,
            'quantity' => (float) $this->stockMovementRequest->quantity,
            'product_name' => $this->stockMovementRequest->product?->name ?? 'Unknown product',
            'location_name' => $this->stockMovementRequest->location?->name ?? 'Unknown location',
            'requested_by_name' => $this->stockMovementRequest->requestedBy?->name ?? 'A staff member',
            'message' => $this->buildSummary(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Stock request awaiting your approval')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line($this->buildSummary())
            ->line('This request is now pending review.')
            ->action('Review Request', url('/stock-movement-requests'))
            ->line('You are receiving this because you can approve stock movement requests.');
    }

    private function buildSummary(): string
    {
        $requester = $this->stockMovementRequest->requestedBy?->name ?? 'A staff member';
        $product = $this->stockMovementRequest->product?->name ?? 'Unknown product';
        $quantity = rtrim(rtrim(number_format((float) $this->stockMovementRequest->quantity, 4), '0'), '.');

        if ($this->stockMovementRequest->isTransfer()) {
            $from = $this->stockMovementRequest->location?->name ?? 'Unknown location';
            $to = $this->stockMovementRequest->destinationLocation?->name ?? 'Unknown location';

            return "{$requester} requested a transfer of {$quantity} {$product} from {$from} to {$to}.";
        }

        $location = $this->stockMovementRequest->location?->name ?? 'Unknown location';
        $action = $this->stockMovementRequest->type === 'in' ? 'add' : 'remove';

        return "{$requester} requested to {$action} {$quantity} {$product} at {$location}.";
    }
}

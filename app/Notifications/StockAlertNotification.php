<?php

namespace App\Notifications;

use App\Models\StockAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockAlertNotification extends Notification
{
    use Queueable;

    public function __construct(public StockAlert $alert)
    {
        $this->alert->loadMissing('inventory.product', 'inventory.location');
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (config('stockalerts.email_enabled')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'stock_alert_id' => $this->alert->id,
            'severity' => $this->alert->severity,
            'status' => $this->alert->status,
            'inventory_id' => $this->alert->inventory_id,
            'message' => 'Stock alert requires attention.',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $inventory = $this->alert->inventory;
        $product = $inventory?->product?->name ?? 'Deleted product';
        $location = $inventory?->location?->name ?? 'Deleted location';

        return (new MailMessage)
            ->subject('Stock alert: ' . str_replace('_', ' ', $this->alert->severity))
            ->greeting('Stock attention required')
            ->line($product . ' at ' . $location . ' is ' . str_replace('_', ' ', $this->alert->severity) . '.')
            ->line('Current base quantity: ' . number_format((float) $this->alert->base_quantity, 4))
            ->line('Reorder point: ' . number_format((float) $this->alert->reorder_point, 4))
            ->action('View Inventory', url('/inventories/' . $inventory?->id));
    }
}

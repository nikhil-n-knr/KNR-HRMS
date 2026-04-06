<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\InventoryItem;

class LowStockNotification extends Notification
{
    use Queueable;

    protected $item;

    public function __construct(InventoryItem $item)
    {
        $this->item = $item;
    }

    public function via(object $notifiable): array
    {
        return ['database']; // Default to database for now
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject("Low Stock Alert: {$this->item->item_name}")
                    ->line("The inventory for {$this->item->item_name} has dropped below the minimum level.")
                    ->line("Current Stock: {$this->item->current_stock}")
                    ->line("Minimum Level: {$this->item->min_stock_level}")
                    ->action('Restock Now', url('/admin/inventory/procurement/restock'))
                    ->line('Please initiate a purchase order.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Low Stock: {$this->item->item_name}",
            'message' => "Current stock ({$this->item->current_stock}) is below minimum ({$this->item->min_stock_level}).",
            'item_id' => $this->item->id,
            'link' => route('admin.store.procurement.restock'),
            'type' => 'alert'
        ];
    }
}

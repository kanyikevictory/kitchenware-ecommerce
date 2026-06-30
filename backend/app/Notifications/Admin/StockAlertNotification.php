<?php

namespace App\Notifications\Admin;

use App\Models\Product;
use App\Notifications\QueuedMailNotification;
use Illuminate\Notifications\Messages\MailMessage;

class StockAlertNotification extends QueuedMailNotification
{
    public function __construct(public readonly Product $product)
    {
        parent::__construct();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $outOfStock = $this->product->stock_quantity === 0;

        return (new MailMessage)
            ->subject($outOfStock ? 'Product out of stock' : 'Low stock alert')
            ->line("{$this->product->name} ({$this->product->sku}) has {$this->product->stock_quantity} units remaining.")
            ->action('Manage Product', rtrim((string) config('app.frontend_url'), '/').'/admin/products/'.$this->product->id);
    }
}

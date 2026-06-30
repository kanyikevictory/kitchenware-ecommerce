<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Models\Product;
use App\Models\User;
use App\Notifications\Admin\NewOrderNotification;
use App\Notifications\Admin\StockAlertNotification;
use App\Notifications\Customer\OrderConfirmationNotification;
use Illuminate\Support\Facades\Notification;

class SendOrderPlacedNotifications
{
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order->loadMissing(['user', 'items']);
        $order->user?->notify(new OrderConfirmationNotification($order));
        $administrators = $this->administrators();
        Notification::send($administrators, new NewOrderNotification($order));

        $productIds = $order->items->pluck('product_id')->filter();
        $products = Product::query()->whereIn('id', $productIds)
            ->where('stock_quantity', '<=', config('inventory.low_stock_threshold'))->get();

        foreach ($products as $product) {
            Notification::send($administrators, new StockAlertNotification($product));
        }
    }

    private function administrators()
    {
        return User::query()->where('status', 'active')
            ->whereHas('role', fn ($query) => $query->whereIn('slug', ['admin', 'super-admin']))
            ->get();
    }
}

<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function metrics(int $year): array
    {
        return [
            'summary' => $this->summary(),
            'order_statuses' => $this->orderStatuses(),
            'low_stock' => $this->lowStock(),
            'best_sellers' => $this->bestSellers(),
            'monthly_sales' => $this->monthlySales($year),
        ];
    }

    private function summary(): array
    {
        $threshold = (int) config('inventory.low_stock_threshold', 5);

        return [
            'total_sales' => Order::query()->where('status', 'delivered')->count(),
            'revenue' => $this->money(Payment::query()->where('status', 'completed')->sum('amount')),
            'orders' => Order::query()->count(),
            'customers' => User::query()->whereHas('role', fn ($query) => $query->where('slug', 'customer'))->count(),
            'products' => Product::query()->where('status', 'active')->count(),
            'categories' => Category::query()->where('is_active', true)->count(),
            'low_stock_products' => Product::query()->where('status', 'active')
                ->whereBetween('stock_quantity', [1, $threshold])->count(),
            'out_of_stock_products' => Product::query()->where('status', 'active')->where('stock_quantity', 0)->count(),
        ];
    }

    private function orderStatuses(): array
    {
        $counts = Order::query()->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')->pluck('total', 'status');

        return collect(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])
            ->mapWithKeys(fn (string $status): array => [$status => (int) ($counts[$status] ?? 0)])
            ->all();
    }

    private function lowStock(): array
    {
        $threshold = (int) config('inventory.low_stock_threshold', 5);

        return Product::query()->where('status', 'active')
            ->where('stock_quantity', '<=', $threshold)
            ->orderBy('stock_quantity')->orderBy('name')->limit(10)
            ->get(['id', 'name', 'sku', 'stock_quantity'])
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'stock_quantity' => $product->stock_quantity,
                'is_out_of_stock' => $product->stock_quantity === 0,
            ])->all();
    }

    private function bestSellers(): array
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->select('order_items.product_id', 'order_items.product_name')
            ->selectRaw('SUM(order_items.quantity) as quantity_sold')
            ->selectRaw('SUM(order_items.total_price) as revenue')
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('quantity_sold')->limit(10)->get()
            ->map(fn (OrderItem $item): array => [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity_sold' => (int) $item->getAttribute('quantity_sold'),
                'revenue' => $this->money($item->getAttribute('revenue')),
            ])->all();
    }

    private function monthlySales(int $year): array
    {
        $monthExpression = DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', paid_at) AS INTEGER)"
            : 'MONTH(paid_at)';

        $sales = Payment::query()->where('status', 'completed')->whereYear('paid_at', $year)
            ->selectRaw("{$monthExpression} as month, SUM(amount) as revenue")
            ->groupByRaw($monthExpression)->pluck('revenue', 'month');

        return collect(range(1, 12))->map(fn (int $month): array => [
            'month' => $month,
            'revenue' => $this->money($sales[$month] ?? 0),
        ])->all();
    }

    private function money(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}

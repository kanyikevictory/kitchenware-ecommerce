<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SearchService
{
    public function search(array $filters): array
    {
        $type = $filters['type'] ?? 'all';
        $perPage = (int) ($filters['per_page'] ?? 12);

        return [
            'query' => $filters['q'],
            'type' => $type,
            'products' => in_array($type, ['all', 'products'], true)
                ? $this->products($filters, $perPage)
                : null,
            'categories' => in_array($type, ['all', 'categories'], true)
                ? $this->categories($filters, $perPage)
                : null,
        ];
    }

    private function products(array $filters, int $perPage): LengthAwarePaginator
    {
        $term = $filters['q'];
        $sorts = [
            'newest' => ['created_at', 'desc'],
            'price_asc' => ['effective_price', 'asc'],
            'price_desc' => ['effective_price', 'desc'],
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
        ];
        [$sortColumn, $sortDirection] = $sorts[$filters['sort'] ?? 'newest'];

        $query = Product::query()
            ->where('status', 'active')
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true))
            ->where(function (Builder $query) use ($term): void {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            })
            ->with([
                'category:id,name,slug',
                'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order'),
            ])
            ->when(isset($filters['category_id']), fn (Builder $query) => $query->where('category_id', $filters['category_id']))
            ->when(isset($filters['brand']), fn (Builder $query) => $query->where('brand', $filters['brand']))
            ->when(isset($filters['min_price']), fn (Builder $query) => $this->whereEffectivePrice($query, '>=', $filters['min_price']))
            ->when(isset($filters['max_price']), fn (Builder $query) => $this->whereEffectivePrice($query, '<=', $filters['max_price']))
            ->when(($filters['in_stock'] ?? false) === true, fn (Builder $query) => $query->where('stock_quantity', '>', 0))
            ->when(($filters['featured'] ?? false) === true, fn (Builder $query) => $query->where('is_featured', true));

        if ($sortColumn === 'effective_price') {
            $query->orderByRaw("COALESCE(discount_price, price) {$sortDirection}");
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        return $query->paginate($perPage, ['*'], 'product_page', $filters['product_page'] ?? 1)
            ->withQueryString();
    }

    private function whereEffectivePrice(Builder $query, string $operator, float|int $amount): void
    {
        $query->where(function (Builder $query) use ($operator, $amount): void {
            $query->where(function (Builder $query) use ($operator, $amount): void {
                $query->whereNotNull('discount_price')->where('discount_price', $operator, $amount);
            })->orWhere(function (Builder $query) use ($operator, $amount): void {
                $query->whereNull('discount_price')->where('price', $operator, $amount);
            });
        });
    }

    private function categories(array $filters, int $perPage): LengthAwarePaginator
    {
        $term = $filters['q'];

        return Category::query()->where('is_active', true)
            ->where(function (Builder $query) use ($term): void {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            })
            ->with('parent:id,name,slug')
            ->withCount(['products' => fn (Builder $query) => $query->where('status', 'active')])
            ->when(isset($filters['parent_id']), fn (Builder $query) => $query->where('parent_id', $filters['parent_id']))
            ->orderBy('sort_order')->orderBy('name')
            ->paginate($perPage, ['*'], 'category_page', $filters['category_page'] ?? 1)
            ->withQueryString();
    }
}

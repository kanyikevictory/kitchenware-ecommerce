<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $search = trim((string) $request->query('search'));
        $sorts = [
            'newest' => ['created_at', 'desc'],
            'price_asc' => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
        ];
        [$sortColumn, $sortDirection] = $sorts[$request->query('sort', 'newest')] ?? $sorts['newest'];

        $products = Product::query()
            ->where('status', 'active')
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true))
            ->with(['category:id,name,slug', 'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')])
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when($request->filled('category_id'), fn (Builder $query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('brand'), fn (Builder $query) => $query->where('brand', $request->query('brand')))
            ->when($request->filled('min_price'), fn (Builder $query) => $query->where('price', '>=', max(0, $request->float('min_price'))))
            ->when($request->filled('max_price'), fn (Builder $query) => $query->where('price', '<=', max(0, $request->float('max_price'))))
            ->when($request->boolean('in_stock'), fn (Builder $query) => $query->where('stock_quantity', '>', 0))
            ->when($request->boolean('featured'), fn (Builder $query) => $query->where('is_featured', true))
            ->orderBy($sortColumn, $sortDirection)
            ->paginate($perPage)->withQueryString();

        return ProductResource::collection($products);
    }

    public function show(Product $product): ProductResource
    {
        $product->load([
            'category:id,name,slug,is_active',
            'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order'),
        ]);

        abort_unless($product->status === 'active' && $product->category?->is_active, 404);

        return new ProductResource($product);
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreProductRequest;
use App\Http\Requests\Api\V1\Admin\UpdateProductRequest;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('manage', Product::class);
        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $search = trim((string) $request->query('search'));

        $products = Product::query()->with(['category:id,name,slug', 'images'])
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            }))
            ->when($request->filled('category_id'), fn (Builder $query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->query('status')))
            ->latest()->paginate($perPage)->withQueryString();

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        Gate::authorize('manage', Product::class);
        $product = $this->productService->create($request->validated(), $request->file('images', []));

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => new ProductResource($this->loadProduct($product)),
        ], Response::HTTP_CREATED);
    }

    public function show(Product $product): ProductResource
    {
        Gate::authorize('manage', $product);

        return new ProductResource($this->loadProduct($product));
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        Gate::authorize('manage', $product);
        $product = $this->productService->update($product, $request->validated(), $request->file('images', []));

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => new ProductResource($this->loadProduct($product)),
        ]);
    }

    public function destroy(Product $product): Response
    {
        Gate::authorize('manage', $product);
        $this->productService->delete($product);

        return response()->noContent();
    }

    private function loadProduct(Product $product): Product
    {
        return $product->load([
            'category:id,name,slug',
            'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order'),
        ]);
    }
}

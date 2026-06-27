<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ProductImageController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function destroy(Product $product, ProductImage $image): Response
    {
        Gate::authorize('manage', $product);
        $this->productService->deleteImage($product, $image);

        return response()->noContent();
    }

    public function primary(Product $product, ProductImage $image): JsonResponse
    {
        Gate::authorize('manage', $product);
        $this->productService->setPrimaryImage($product, $image);

        return response()->json(['message' => 'Primary image updated successfully.']);
    }
}

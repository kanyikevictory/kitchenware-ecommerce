<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductReviewController extends Controller
{
    public function index(Request $request, Product $product): AnonymousResourceCollection
    {
        abort_unless($product->status === 'active', 404);
        $perPage = min(max($request->integer('per_page', 10), 1), 100);
        $query = Review::query()->where('product_id', $product->id)->where('is_approved', true);
        $summary = (clone $query)->selectRaw('COUNT(*) as total, COALESCE(AVG(rating), 0) as average')->first();
        $reviews = $query->with('user:id,name')->latest()->paginate($perPage)->withQueryString();

        return ReviewResource::collection($reviews)->additional([
            'summary' => [
                'average_rating' => round((float) $summary->average, 2),
                'reviews_count' => (int) $summary->total,
            ],
        ]);
    }
}

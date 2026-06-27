<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Review\StoreReviewRequest;
use App\Http\Requests\Api\V1\Review\UpdateReviewRequest;
use App\Http\Resources\Api\V1\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviewService) {}

    public function store(StoreReviewRequest $request, Product $product): JsonResponse
    {
        $review = $this->reviewService->create($request->user(), $product, $request->validated());

        return response()->json([
            'message' => 'Review submitted for moderation.',
            'data' => new ReviewResource($review->load('user:id,name')),
        ], Response::HTTP_CREATED);
    }

    public function update(UpdateReviewRequest $request, Review $review): JsonResponse
    {
        Gate::authorize('update', $review);
        $review = $this->reviewService->update($review, $request->validated());

        return response()->json([
            'message' => 'Review updated and returned to moderation.',
            'data' => new ReviewResource($review->load('user:id,name')),
        ]);
    }

    public function destroy(Review $review): Response
    {
        Gate::authorize('delete', $review);
        $review->delete();

        return response()->noContent();
    }
}

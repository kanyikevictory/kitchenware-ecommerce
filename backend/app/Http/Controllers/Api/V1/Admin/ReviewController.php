<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\ModerateReviewRequest;
use App\Http\Resources\Api\V1\ReviewResource;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('manage', Review::class);
        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $search = trim((string) $request->query('search'));

        $reviews = Review::query()->with(['user:id,name', 'product:id,name,slug'])
            ->when($request->filled('is_approved'), fn (Builder $query) => $query->where('is_approved', $request->boolean('is_approved')))
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('comment', 'like', "%{$search}%");
            }))
            ->latest()->paginate($perPage)->withQueryString();

        return ReviewResource::collection($reviews);
    }

    public function moderate(ModerateReviewRequest $request, Review $review): JsonResponse
    {
        Gate::authorize('manage', $review);
        $review->update(['is_approved' => $request->boolean('is_approved')]);

        return response()->json([
            'message' => 'Review moderation status updated.',
            'data' => new ReviewResource($review->load(['user:id,name', 'product:id,name,slug'])),
        ]);
    }
}

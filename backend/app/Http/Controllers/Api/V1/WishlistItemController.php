<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Wishlist\AddWishlistItemRequest;
use App\Http\Resources\Api\V1\WishlistResource;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class WishlistItemController extends Controller
{
    public function __construct(private readonly WishlistService $wishlistService) {}

    public function store(AddWishlistItemRequest $request): JsonResponse
    {
        return $this->response($this->wishlistService->add(
            $request->user(),
            $request->integer('product_id'),
        ));
    }

    public function destroy(WishlistItem $wishlistItem): JsonResponse
    {
        Gate::authorize('delete', $wishlistItem);

        return $this->response($this->wishlistService->remove($wishlistItem));
    }

    private function response(Wishlist $wishlist): JsonResponse
    {
        return (new WishlistResource($wishlist))->response()->setStatusCode(Response::HTTP_OK);
    }
}

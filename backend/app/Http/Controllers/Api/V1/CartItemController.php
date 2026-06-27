<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Cart\AddCartItemRequest;
use App\Http\Requests\Api\V1\Cart\UpdateCartItemRequest;
use App\Http\Resources\Api\V1\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CartItemController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function store(AddCartItemRequest $request): JsonResponse
    {
        $cart = $this->cartService->add(
            $request->user(),
            $request->integer('product_id'),
            $request->integer('quantity'),
        );

        return $this->response($cart);
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        Gate::authorize('update', $cartItem);

        return $this->response($this->cartService->update($cartItem, $request->integer('quantity')));
    }

    public function destroy(CartItem $cartItem): JsonResponse
    {
        Gate::authorize('delete', $cartItem);

        return $this->response($this->cartService->remove($cartItem));
    }

    private function response(Cart $cart): JsonResponse
    {
        return (new CartResource($cart))->response()->setStatusCode(Response::HTTP_OK);
    }
}

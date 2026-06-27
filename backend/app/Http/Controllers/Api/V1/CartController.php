<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CartResource;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function show(Request $request): JsonResponse
    {
        return (new CartResource($this->cartService->get($request->user())))
            ->response()->setStatusCode(Response::HTTP_OK);
    }

    public function destroy(Request $request): JsonResponse
    {
        return (new CartResource($this->cartService->clear($request->user())))
            ->response()->setStatusCode(Response::HTTP_OK);
    }
}

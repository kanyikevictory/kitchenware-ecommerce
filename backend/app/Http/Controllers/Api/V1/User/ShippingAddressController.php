<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\StoreShippingAddressRequest;
use App\Http\Requests\Api\V1\User\UpdateShippingAddressRequest;
use App\Http\Resources\Api\V1\ShippingAddressResource;
use App\Models\ShippingAddress;
use App\Services\ShippingAddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ShippingAddressController extends Controller
{
    public function __construct(private readonly ShippingAddressService $addressService) {}

    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->shippingAddresses()->orderByDesc('is_default')->latest()->get();

        return response()->json(['data' => ShippingAddressResource::collection($addresses)]);
    }

    public function store(StoreShippingAddressRequest $request): JsonResponse
    {
        $address = $this->addressService->create($request->user(), $request->validated());

        return response()->json([
            'message' => 'Shipping address created successfully.',
            'data' => new ShippingAddressResource($address),
        ], Response::HTTP_CREATED);
    }

    public function show(ShippingAddress $shippingAddress): JsonResponse
    {
        Gate::authorize('view', $shippingAddress);

        return response()->json(['data' => new ShippingAddressResource($shippingAddress)]);
    }

    public function update(UpdateShippingAddressRequest $request, ShippingAddress $shippingAddress): JsonResponse
    {
        Gate::authorize('update', $shippingAddress);
        $address = $this->addressService->update($shippingAddress, $request->validated());

        return response()->json(['message' => 'Shipping address updated successfully.', 'data' => new ShippingAddressResource($address)]);
    }

    public function destroy(ShippingAddress $shippingAddress): Response
    {
        Gate::authorize('delete', $shippingAddress);
        $this->addressService->delete($shippingAddress);

        return response()->noContent();
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreCouponRequest;
use App\Http\Requests\Api\V1\Admin\UpdateCouponRequest;
use App\Http\Resources\Api\V1\CouponResource;
use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class CouponController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('manage', Coupon::class);
        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $search = trim((string) $request->query('search'));

        $coupons = Coupon::query()
            ->when($search !== '', fn (Builder $query) => $query->where('code', 'like', "%{$search}%"))
            ->when($request->filled('is_active'), fn (Builder $query) => $query->where('is_active', $request->boolean('is_active')))
            ->latest()->paginate($perPage)->withQueryString();

        return CouponResource::collection($coupons);
    }

    public function store(StoreCouponRequest $request): JsonResponse
    {
        $coupon = Coupon::query()->create($request->validated());

        return response()->json([
            'message' => 'Coupon created successfully.',
            'data' => new CouponResource($coupon),
        ], Response::HTTP_CREATED);
    }

    public function show(Coupon $coupon): CouponResource
    {
        Gate::authorize('manage', $coupon);

        return new CouponResource($coupon);
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): JsonResponse
    {
        Gate::authorize('manage', $coupon);
        $coupon->update($request->validated());

        return response()->json([
            'message' => 'Coupon updated successfully.',
            'data' => new CouponResource($coupon->refresh()),
        ]);
    }

    public function destroy(Coupon $coupon): Response
    {
        Gate::authorize('manage', $coupon);

        if ($coupon->usage_count > 0 || Order::query()->where('coupon_id', $coupon->id)->exists()) {
            throw ValidationException::withMessages([
                'coupon' => ['Used coupons must be deactivated instead of deleted.'],
            ]);
        }

        $coupon->delete();

        return response()->noContent();
    }
}

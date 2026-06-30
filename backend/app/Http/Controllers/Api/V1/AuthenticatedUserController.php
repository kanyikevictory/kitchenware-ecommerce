<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthenticatedUserController
{
    /**
     * Return the authenticated user in a stable API shape.
     */
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()->load('role.permissions')),
        ]);
    }
}

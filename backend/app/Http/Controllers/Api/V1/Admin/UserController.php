<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\UpdateUserStatusRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', User::class);
        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $search = trim((string) $request->query('search'));

        $users = User::query()->with('role')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            }))
            ->latest()->paginate($perPage)->withQueryString();

        return UserResource::collection($users);
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user): JsonResponse
    {
        Gate::authorize('updateStatus', $user);
        $user->update(['status' => $request->validated('status')]);

        if ($user->status === 'inactive') {
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => "User {$user->status} successfully.",
            'data' => new UserResource($user->load('role')),
        ]);
    }
}

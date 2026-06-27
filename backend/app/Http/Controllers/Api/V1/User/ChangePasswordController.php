<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\ChangePasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ChangePasswordController extends Controller
{
    public function __invoke(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->validated('current_password'), $user->password)) {
            throw ValidationException::withMessages(['current_password' => ['The current password is incorrect.']]);
        }

        $currentTokenId = $user->currentAccessToken()?->getKey();
        $user->update(['password' => $request->validated('password')]);

        $currentTokenId
            ? $user->tokens()->whereKeyNot($currentTokenId)->delete()
            : $user->tokens()->delete();

        return response()->json(['message' => 'Password changed successfully.']);
    }
}

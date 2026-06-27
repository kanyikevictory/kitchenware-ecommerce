<?php

use App\Http\Controllers\Api\V1\AuthenticatedUserController;
use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('health', HealthController::class);

    Route::middleware('auth:sanctum')->get('me', AuthenticatedUserController::class);
});

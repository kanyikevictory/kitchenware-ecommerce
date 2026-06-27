<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;

class HealthController
{
    /**
     * Return a small status payload for API uptime checks.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => config('app.name', 'Laravel'),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}

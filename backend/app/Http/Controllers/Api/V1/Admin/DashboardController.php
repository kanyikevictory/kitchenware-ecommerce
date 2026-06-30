<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\DashboardRequest;
use App\Http\Resources\Api\V1\DashboardResource;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService) {}

    public function __invoke(DashboardRequest $request): DashboardResource
    {
        $year = $request->integer('year', now()->year);

        return new DashboardResource($this->dashboardService->metrics($year));
    }
}

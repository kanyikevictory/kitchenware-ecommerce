<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'summary' => $this->resource['summary'],
            'order_statuses' => $this->resource['order_statuses'],
            'low_stock' => $this->resource['low_stock'],
            'best_sellers' => $this->resource['best_sellers'],
            'monthly_sales' => $this->resource['monthly_sales'],
        ];
    }
}

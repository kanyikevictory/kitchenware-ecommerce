<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'query' => $this->resource['query'],
            'type' => $this->resource['type'],
            'products' => $this->formatPaginator($this->resource['products'], ProductResource::class),
            'categories' => $this->formatPaginator($this->resource['categories'], CategoryResource::class),
        ];
    }

    private function formatPaginator(?LengthAwarePaginator $paginator, string $resourceClass): ?array
    {
        if (! $paginator) {
            return null;
        }

        return [
            'data' => $resourceClass::collection($paginator->getCollection()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SearchRequest;
use App\Http\Resources\Api\V1\SearchResource;
use App\Services\SearchService;

class SearchController extends Controller
{
    public function __construct(private readonly SearchService $searchService) {}

    public function __invoke(SearchRequest $request): SearchResource
    {
        return new SearchResource($this->searchService->search($request->validated()));
    }
}

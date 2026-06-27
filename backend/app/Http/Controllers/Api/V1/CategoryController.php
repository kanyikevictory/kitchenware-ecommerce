<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $search = trim((string) $request->query('search'));

        $categories = Category::query()
            ->where('is_active', true)
            ->with('parent:id,name,slug')
            ->withCount(['products' => fn ($query) => $query->where('status', 'active')])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when($request->filled('parent_id'), fn ($query) => $query->where('parent_id', $request->integer('parent_id')))
            ->when($request->boolean('roots_only'), fn ($query) => $query->whereNull('parent_id'))
            ->orderBy('sort_order')->orderBy('name')
            ->paginate($perPage)->withQueryString();

        return CategoryResource::collection($categories);
    }

    public function show(Category $category): CategoryResource
    {
        abort_unless($category->is_active, 404);

        $category->load([
            'parent:id,name,slug',
            'children' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name'),
        ])->loadCount(['products' => fn ($query) => $query->where('status', 'active')]);

        return new CategoryResource($category);
    }
}

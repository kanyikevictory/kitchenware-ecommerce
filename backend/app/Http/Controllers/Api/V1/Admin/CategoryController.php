<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreCategoryRequest;
use App\Http\Requests\Api\V1\Admin\UpdateCategoryRequest;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAnyAdmin', Category::class);
        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $search = trim((string) $request->query('search'));

        $categories = Category::query()->with('parent:id,name,slug')->withCount('products')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy('sort_order')->orderBy('name')
            ->paginate($perPage)->withQueryString();

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        Gate::authorize('create', Category::class);
        $category = $this->categoryService->create($request->validated(), $request->file('image'));

        return response()->json([
            'message' => 'Category created successfully.',
            'data' => new CategoryResource($category->load('parent:id,name,slug')->loadCount('products')),
        ], Response::HTTP_CREATED);
    }

    public function show(Category $category): CategoryResource
    {
        Gate::authorize('viewAnyAdmin', Category::class);

        return new CategoryResource($category->load('parent:id,name,slug', 'children')->loadCount('products'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        Gate::authorize('update', $category);
        $category = $this->categoryService->update($category, $request->validated(), $request->file('image'));

        return response()->json([
            'message' => 'Category updated successfully.',
            'data' => new CategoryResource($category->load('parent:id,name,slug')->loadCount('products')),
        ]);
    }

    public function destroy(Category $category): Response
    {
        Gate::authorize('delete', $category);
        $this->categoryService->delete($category);

        return response()->noContent();
    }
}

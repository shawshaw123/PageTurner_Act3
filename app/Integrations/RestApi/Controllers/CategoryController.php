<?php

namespace App\Integrations\RestApi\Controllers;

use App\Http\Controllers\Controller;
use App\Integrations\RestApi\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /** GET /api/v1/categories — public. books_count counts visible listings. */
    public function index()
    {
        return CategoryResource::collection(
            Category::withCount(['books' => fn ($q) => $q->visible()])->orderBy('name')->get()
        );
    }

    /** GET /api/v1/categories/{category} */
    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category->loadCount(['books' => fn ($q) => $q->visible()]));
    }

    /** POST /api/v1/categories — admins only. */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Category::class);

        $category = Category::create($request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
        ]));

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    /** PUT/PATCH /api/v1/categories/{category} — admins only. */
    public function update(Request $request, Category $category): CategoryResource
    {
        Gate::authorize('update', $category);

        $category->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string'],
        ]));

        return new CategoryResource($category);
    }

    /** DELETE /api/v1/categories/{category} — admins only. */
    public function destroy(Category $category): JsonResponse
    {
        Gate::authorize('delete', $category);

        $category->delete();

        return response()->json(null, 204);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\DTO\Category\StoreCategoryDTO;
use App\DTO\Category\UpdateCategoryDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\Category\CategoryCollection;
use App\Http\Resources\Category\CategoryResource;
use App\Services\Category\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Categories
 */
class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService,
    ) {}

    /**
     * List all categories (global defaults + user's own).
     */
    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryService->listForUser($request->user()->id);

        return response()->json(
            (new CategoryCollection($categories))
                ->additional(['success' => true, 'message' => 'Daftar kategori.'])
                ->toResponse($request)
                ->getData(true),
            200
        );
    }

    /**
     * Create a custom category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $dto      = StoreCategoryDTO::fromRequest($request->validated(), $request->user()->id);
        $category = $this->categoryService->store($dto);

        return $this->created(
            new CategoryResource($category),
            'Kategori berhasil dibuat.'
        );
    }

    /**
     * Search category for user owner.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:1', 'max:100'],
        ]);
 
        $results = $this->categoryService->searchForUser(
            $request->query('q'),
            $request->user()->id,
        );
 
        return $this->ok(
            CategoryResource::collection($results),
            'Hasil pencarian kategori.'
        );
    }

    /**
     * Get category detail.
     *
     * @urlParam id string required Category UUID.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $category = $this->categoryService->findOrFail($id, $request->user()->id);

        return $this->ok(
            new CategoryResource($category),
            'Detail kategori.'
        );
    }

    /**
     * Update a user-owned category.
     *
     * @urlParam id string required Category UUID.
     */
    public function update(UpdateCategoryRequest $request, string $id): JsonResponse
    {
        $dto      = UpdateCategoryDTO::fromRequest($request->validated());
        $category = $this->categoryService->update($id, $request->user()->id, $dto);

        return $this->ok(
            new CategoryResource($category),
            'Kategori berhasil diperbarui.'
        );
    }

    /**
     * Delete a user-owned category.
     *
     * @urlParam id string required Category UUID.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->categoryService->delete($id, $request->user()->id);

        return $this->noContent('Kategori berhasil dihapus.');
    }
}

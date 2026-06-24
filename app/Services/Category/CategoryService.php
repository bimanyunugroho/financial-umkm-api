<?php

namespace App\Services\Category;

use App\DTO\Category\StoreCategoryDTO;
use App\DTO\Category\UpdateCategoryDTO;
use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\ResourceNotFoundException;
use App\Repositories\Interfaces\CategoryInterface;
use Illuminate\Support\Collection;
use App\Models\Category;

class CategoryService
{
    public function __construct(
        private readonly CategoryInterface $categoryRepo,
    ) {}

    public function listForUser(string $userId): Collection
    {
        return $this->categoryRepo->allForUser($userId);
    }

    public function findOrFail(string $id, string $userId): Category
    {
        $category = $this->categoryRepo->findForUser($id, $userId);

        if (! $category) {
            throw new ResourceNotFoundException('Kategori tidak ditemukan');
        }

        return $category;
    }

    public function searchForUser(string $search, string $userId): Collection
    {
        return $this->categoryRepo->searchOwnedByUser($search, $userId);
    }

    public function store(StoreCategoryDTO $dto): Category
    {
        return $this->categoryRepo->create($dto->toArray());
    }

    public function update(string $id, string $userId, UpdateCategoryDTO $dto): Category
    {
        $category = $this->categoryRepo->findOwnedByUser($id, $userId);

        if (! $category) {
            throw new ForbiddenException('Kategori tidak ditemukan atau tidak dapat diedit.');
        }

        return $this->categoryRepo->update($category, $dto->toArray());
    }

    public function delete(string $id, string $userId): void
    {
        $category = $this->categoryRepo->findOwnedByUser($id, $userId);

        if (! $category) {
            throw new ForbiddenException('Kategori tidak ditemukan atau tidak dapat dihapus.');
        }

        if ($this->categoryRepo->hasTransactions($category)) {
            throw new ConflictException('Kategori tidak dapat dihapus karena masih memiliki transaksi.');
        }

        $this->categoryRepo->delete($category);
    }
}

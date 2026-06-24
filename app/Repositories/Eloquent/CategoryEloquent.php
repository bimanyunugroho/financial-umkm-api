<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryInterface;
use Illuminate\Support\Collection;

class CategoryEloquent implements CategoryInterface
{
    public function allForUser(string $userId): Collection
    {
        return Category::forUser($userId)
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }

    public function findForUser(string $id, string $userId): ?Category
    {
        return Category::forUser($userId)->find($id);
    }

    public function findOwnedByUser(string $id, string $userId): ?Category
    {
        return Category::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function searchOwnedByUser(string $search, string $userId): Collection
    {
        return Category::where('user_id', $userId)
            ->where('name', 'ilike', '%' . $search . '%')
            ->orderBy('type')->orderBy('name')->get();
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        return $category->fresh();
    }

    public function delete(Category $category): bool
    {
        return (bool) $category->delete();
    }

    public function hasTransactions(Category $category): bool
    {
        return $category->transactions()->exists();
    }
}

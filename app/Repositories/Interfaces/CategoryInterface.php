<?php

namespace App\Repositories\Interfaces;

use App\Models\Category;
use Illuminate\Support\Collection;

interface CategoryInterface {

    public function allForUser(string $userId): Collection;

    public function findForUser(string $id, string $userId): ?Category;

    public function findOwnedByUser(string $id, string $userId): ?Category;

    public function searchOwnedByUser(string $search, string $userId): Collection;

    public function create(array $data): Category;

    public function update(Category $category, array $data): Category;

    public function delete(Category $category): bool;

    public function hasTransactions(Category $category): bool;
}
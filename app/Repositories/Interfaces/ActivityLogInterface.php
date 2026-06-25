<?php

namespace App\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ActivityLogInterface
{
    public function paginateForUser(string $userId, array $filters, int $perPage): LengthAwarePaginator;

    public function summaryForUser(string $userId, int $days): Collection;

    public function forSubject(string $userId, string $subjectType, string $subjectId): Collection;
}

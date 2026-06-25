<?php

namespace App\Services\Activity;

use App\Repositories\Interfaces\ActivityLogInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ActivityLogService
{
    public function __construct(
        private readonly ActivityLogInterface $activityRepo,
    ) {}

    public function list(string $userId, array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->activityRepo->paginateForUser($userId, $filters, min($perPage, 50));
    }

    public function summary(string $userId, int $days = 30): array
    {
        $activities = $this->activityRepo->summaryForUser($userId, $days);

        return [
            'period'     => "last_{$days}_days",
            'activities' => $activities->map(fn ($row) => [
                'event'        => $row->event,
                'subject_type' => class_basename($row->subject_type ?? ''),
                'count'        => (int) $row->count,
                'last_at'      => $row->last_at,
            ]),
            'total' => (int) $activities->sum('count'),
        ];
    }

    public function forSubject(string $userId, string $subjectType, string $subjectId): Collection
    {
        return $this->activityRepo->forSubject($userId, $subjectType, $subjectId);
    }
}

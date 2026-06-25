<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\ActivityLogInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

class ActivityLogEloquent implements ActivityLogInterface
{
    public function paginateForUser(string $userId, array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Activity::where('causer_type', 'App\Models\User')
            ->where('causer_id', $userId)
            ->with('causer:id,name,email')
            ->latest();

        if (! empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        if (! empty($filters['subject_type'])) {
            $query->where('subject_type', 'App\\Models\\' . ucfirst($filters['subject_type']));
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function summaryForUser(string $userId, int $days): Collection
    {
        return Activity::where('causer_type', 'App\Models\User')
            ->where('causer_id', $userId)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('event, subject_type, COUNT(*) as count, MAX(created_at) as last_at')
            ->groupBy('event', 'subject_type')
            ->get();
    }

    public function forSubject(string $userId, string $subjectType, string $subjectId): Collection
    {
        return Activity::where('causer_type', 'App\Models\User')
            ->where('causer_id', $userId)
            ->where('subject_type', 'App\\Models\\' . ucfirst($subjectType))
            ->where('subject_id', $subjectId)
            ->latest()
            ->get();
    }
}

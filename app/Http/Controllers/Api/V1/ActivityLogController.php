<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Activity\ActivityCollection;
use App\Http\Resources\Activity\ActivityResource;
use App\Services\Activity\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Activity Log
 */
class ActivityLogController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityService,
    ) {}

    /**
     * List paginated activity log for the authenticated user.
     *
     * @queryParam event string Filter by event: created|updated|deleted. Example: updated
     * @queryParam subject_type string Filter by model: Transaction|Category|User. Example: Transaction
     * @queryParam per_page int Items per page (max 50). Default: 20.
     */
    public function index(Request $request): JsonResponse
    {
        $filters  = $request->only(['event', 'subject_type']);
        $perPage = (int) $request->input('per_page', 20);
        $paginator = $this->activityService->list($request->user()->id, $filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat aktivitas.',
            'data'    => (new ActivityCollection(collect($paginator->items())))
            ->resolve($request),
            'meta'    => [
                'current_page'  => $paginator->currentPage(),
                'per_page'      => $paginator->perPage(),
                'total'         => $paginator->total(),
                'last_page'     => $paginator->lastPage(),
                'from'          => $paginator->firstItem(),
                'to'            => $paginator->lastItem(),
                'has_more'      => $paginator->hasMorePages(),
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_page_url' => $paginator->previousPageUrl(),
            ],
        ], 200);
    }

    /**
     * Activity summary stats for the last N days.
     *
     * @queryParam days int Number of days to look back (default: 30).
     */
    public function summary(Request $request): JsonResponse
    {
        $days = min((int) $request->get('days', 30), 90);
        $data = $this->activityService->summary($request->user()->id, $days);

        return $this->ok($data, 'Ringkasan aktivitas.');
    }

    /**
     * Activity log for a specific subject (e.g. one transaction).
     *
     * @queryParam subject_id string required UUID of the subject. Example: uuid-here
     * @queryParam subject_type string required Model type: Transaction|Category|User.
     */
    public function forSubject(Request $request): JsonResponse
    {
        $request->validate([
            'subject_id'   => ['required', 'uuid'],
            'subject_type' => ['required', 'string', 'in:Transaction,Category,User'],
        ]);

        $logs = $this->activityService->forSubject(
            $request->user()->id,
            $request->query('subject_type'),
            $request->query('subject_id'),
        );

        return $this->ok(
            ActivityResource::collection($logs),
            'Riwayat aktivitas resource.'
        );
    }
}

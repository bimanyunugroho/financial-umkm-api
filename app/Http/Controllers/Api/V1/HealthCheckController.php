<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * @tags Health
 */
class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks  = [];
        $healthy = true;

        // DATABASE
        try {
            $start = microtime(true);
            DB::select('SELECT 1');

            $checks['database'] = [
                'status'  => 'ok',
                'latency' => round((microtime(true) - $start) * 1000, 2) . 'ms',
            ];
        } catch (\Throwable $e) {
            $checks['database'] = ['status' => 'fail', 'error' => 'Database connection failed'];
            $healthy = false;
        }

        // REDIS
        try {
            $start = microtime(true);
            Cache::put('_health_ping', 1, 5);
            Cache::get('_health_ping');

            $checks['redis'] = [
                'status'  => 'ok',
                'latency' => round((microtime(true) - $start) * 1000, 2) . 'ms',
            ];
        } catch (\Throwable $e) {
            $checks['redis'] = ['status' => 'fail', 'error' => 'Redis connection failed'];
            $healthy = false;
        }

        // QUEUE
        try {
            $pending = Redis::connection('queue')->llen('queues:default') ?? 0;
            $checks['queue'] = [
                'status'       => 'ok',
                'pending_jobs' => (int) $pending,
            ];
        } catch (\Throwable $e) {
            $checks['queue'] = ['status' => 'degraded', 'error' => 'Queue check failed'];
        }

        // DISK
        $free  = disk_free_space(storage_path());
        $total = disk_total_space(storage_path());
        $usedPct = $total > 0 ? round((1 - $free / $total) * 100, 1) : 0;

        $checks['disk'] = [
            'status'   => $usedPct < 85 ? 'ok' : ($usedPct < 95 ? 'warning' : 'critical'),
            'used_pct' => $usedPct . '%',
            'free_mb'  => round($free / 1024 / 1024, 1) . 'MB',
        ];

        return response()->json([
            'success'   => $healthy,
            'message'   => $healthy 
                ? 'Semua sistem berjalan normal.' 
                : 'Terdapat masalah pada sistem.',
            'data'      => [
                'status'    => $healthy ? 'healthy' : 'unhealthy',
                'service'   => config('app.name'),
                'version'   => 'v1',
                'timestamp' => now()->toIso8601String(),
                'checks'    => $checks,
            ],
        ], $healthy ? 200 : 503);
    }
}
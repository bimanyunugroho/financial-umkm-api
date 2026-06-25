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
        $checks = [];

        /*
        |--------------------------------------------------------------------------
        | Database
        |--------------------------------------------------------------------------
        */
        try {
            $start = microtime(true);

            DB::select('SELECT 1');

            $latency = round((microtime(true) - $start) * 1000, 2);

            $status = match (true) {
                $latency > 1000 => 'unhealthy',
                $latency > 500  => 'degraded',
                default         => 'healthy',
            };

            $checks['database'] = [
                'status'     => $status,
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            $checks['database'] = [
                'status' => 'unhealthy',
                'error'  => $e->getMessage(),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Redis / Cache
        |--------------------------------------------------------------------------
        */
        try {
            $start = microtime(true);

            Cache::put('_health_ping', now()->timestamp, 5);
            $value = Cache::get('_health_ping');

            $latency = round((microtime(true) - $start) * 1000, 2);

            $status = match (true) {
                $value === null => 'unhealthy',
                $latency > 500  => 'degraded',
                default         => 'healthy',
            };

            $checks['redis'] = [
                'status'     => $status,
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            $checks['redis'] = [
                'status' => 'unhealthy',
                'error'  => $e->getMessage(),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Queue (Redis)
        |--------------------------------------------------------------------------
        */
        try {
            $redis  = Redis::connection('queue');
            $prefix = config('database.redis.options.prefix', '');
            $queue  = 'exports';

            $pendingKey   = "{$prefix}queues:{$queue}";
            $reservedKey  = "{$prefix}queues:{$queue}:reserved";
            $delayedKey   = "{$prefix}queues:{$queue}:delayed";

            $pending    = (int) $redis->llen($pendingKey);
            $processing = (int) $redis->zcard($reservedKey);
            $delayed    = (int) $redis->zcard($delayedKey);

            $total = $pending + $processing + $delayed;

            $status = match (true) {
                $pending > 1000 => 'unhealthy',
                $pending > 100  => 'degraded',
                default         => 'healthy',
            };

            $checks['queue'] = [
                'status' => $status,
                'name'   => $queue,
                'jobs'   => [
                    'pending'    => $pending,
                    'processing' => $processing,
                    'delayed'    => $delayed,
                    'total'      => $total,
                ],
            ];
        } catch (\Throwable $e) {
            $checks['queue'] = [
                'status' => 'unhealthy',
                'error'  => $e->getMessage(),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Disk Storage
        |--------------------------------------------------------------------------
        */
        try {
            $free  = disk_free_space(storage_path());
            $total = disk_total_space(storage_path());

            $usedPct = $total > 0
                ? round((1 - ($free / $total)) * 100, 2)
                : 0;

            $status = match (true) {
                $usedPct >= 95 => 'unhealthy',
                $usedPct >= 85 => 'degraded',
                default        => 'healthy',
            };

            $checks['disk'] = [
                'status'   => $status,
                'used_pct' => $usedPct,
                'free_gb'  => round($free / 1024 / 1024 / 1024, 2),
                'total_gb' => round($total / 1024 / 1024 / 1024, 2),
            ];
        } catch (\Throwable $e) {
            $checks['disk'] = [
                'status' => 'unhealthy',
                'error'  => $e->getMessage(),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Memory Usage
        |--------------------------------------------------------------------------
        */
        $memoryUsedMb = round(memory_get_usage(true) / 1024 / 1024, 2);
        $memoryPeakMb = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

        $checks['memory'] = [
            'status'       => 'healthy',
            'used_mb'      => $memoryUsedMb,
            'peak_used_mb' => $memoryPeakMb,
        ];

        /*
        |--------------------------------------------------------------------------
        | Overall Status
        |--------------------------------------------------------------------------
        */
        $overallStatus = 'healthy';

        foreach ($checks as $check) {
            if (($check['status'] ?? null) === 'unhealthy') {
                $overallStatus = 'unhealthy';
                break;
            }

            if (($check['status'] ?? null) === 'degraded') {
                $overallStatus = 'degraded';
            }
        }

        return response()->json([
            'success' => $overallStatus !== 'unhealthy',
            'message' => match ($overallStatus) {
                'healthy'   => 'All systems operational.',
                'degraded'  => 'System is operational with warnings.',
                'unhealthy' => 'One or more critical services are unavailable.',
            },
            'data' => [
                'status'      => $overallStatus,
                'service'     => config('app.name'),
                'environment' => config('app.env'),
                'version'     => 'v1',
                'timestamp'   => now()->toIso8601String(),
                'checks'      => $checks,
            ],
        ], match ($overallStatus) {
            'healthy'   => 200,
            'degraded'  => 200,
            'unhealthy' => 503,
        });
    }
}
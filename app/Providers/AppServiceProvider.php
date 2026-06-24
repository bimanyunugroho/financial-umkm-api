<?php

namespace App\Providers;

use App\Repositories\Eloquent\CategoryEloquent;
use App\Repositories\Eloquent\UserEloquent;
use App\Repositories\Interfaces\CategoryInterface;
use App\Repositories\Interfaces\UserInterface;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserInterface::class, UserEloquent::class);
        $this->app->bind(CategoryInterface::class, CategoryEloquent::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiters();
        $this->enrichActivityLog();
    }

    // ── Rate Limiters ──────────────────────────────────────────────────────

    private function configureRateLimiters(): void
    {
        // General API — 60 req/min per authenticated user, fallback to IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(
                (int) config('app.rate_limit_api', 60)
            )->by($request->user()?->id ?? $request->ip())
             ->response(fn () => response()->json([
                 'success' => false,
                 'message' => 'Terlalu banyak request. Coba lagi dalam 1 menit.',
             ], 429));
        });

        // Auth endpoints — 5 req/min per IP (brute-force protection)
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(
                (int) config('app.rate_limit_auth', 5)
            )->by($request->ip())
             ->response(fn () => response()->json([
                 'success' => false,
                 'message' => 'Terlalu banyak percobaan. Coba lagi dalam 1 menit.',
             ], 429));
        });

        // Export — 5 req/hour per user (heavy operation)
        RateLimiter::for('export', function (Request $request) {
            return Limit::perHour(5)
                ->by($request->user()?->id ?? $request->ip())
                ->response(fn () => response()->json([
                    'success' => false,
                    'message' => 'Batas export 5x per jam tercapai.',
                ], 429));
        });

        // AI — 20 req/hour per user (costs money)
        RateLimiter::for('ai', function (Request $request) {
            return Limit::perHour(20)
                ->by($request->user()?->id ?? $request->ip())
                ->response(fn () => response()->json([
                    'success' => false,
                    'message' => 'Batas AI insight 20x per jam tercapai.',
                ], 429));
        });
    }

    // ── Activity Log Enrichment ────────────────────────────────────────────

    private function enrichActivityLog(): void
    {
        // Attach IP, user-agent, and URL to every activity log entry
        Activity::saving(function (Activity $activity) {
            $req = request();
            if (! $req) return;

            $activity->properties = array_merge(
                $activity->properties->toArray(),
                [
                    'ip'         => $req->ip(),
                    'user_agent' => $req->userAgent(),
                    'url'        => $req->fullUrl(),
                    'method'     => $req->method(),
                ]
            );
        });
    }
}

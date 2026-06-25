<?php

namespace App\Providers;

use App\Repositories\Eloquent\ActivityLogEloquent;
use App\Repositories\Eloquent\CategoryEloquent;
use App\Repositories\Eloquent\TransactionEloquent;
use App\Repositories\Eloquent\UserEloquent;
use App\Repositories\Interfaces\ActivityLogInterface;
use App\Repositories\Interfaces\CategoryInterface;
use App\Repositories\Interfaces\TransactionInterface;
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
        $this->app->bind(TransactionInterface::class, TransactionEloquent::class);
        $this->app->bind(ActivityLogInterface::class, ActivityLogEloquent::class);
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
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(
                (int) config('app.rate_limit_api', 60)
            )->by($request->user()?->id ?? $request->ip());
        });
 
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(
                (int) config('app.rate_limit_auth', 5)
            )->by($request->ip());
        });
 
        RateLimiter::for('export', function (Request $request) {
            return Limit::perHour(5)
                ->by($request->user()?->id ?? $request->ip());
        });
 
        RateLimiter::for('ai', function (Request $request) {
            return Limit::perHour(10)
                ->by($request->user()?->id ?? $request->ip());
        });
    }
 
    private function enrichActivityLog(): void
    {
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

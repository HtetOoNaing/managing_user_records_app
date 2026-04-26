<?php

namespace App\Providers;

use App\Jobs\WriteUserActivityLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (Login $event): void {
            dispatch(new WriteUserActivityLog(
                userId: (int) $event->user->id,
                event: WriteUserActivityLog::EVENT_USER_LOGIN,
                data: [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ));
        });

        Event::listen(function (Logout $event): void {
            dispatch(new WriteUserActivityLog(
                userId: (int) $event->user->id,
                event: WriteUserActivityLog::EVENT_USER_LOGOUT,
                data: [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ));
        });
    }
}

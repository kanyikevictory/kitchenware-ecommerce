<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(120)
            ->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('admin', fn (Request $request) => Limit::perMinute(120)
            ->by((string) ($request->user()?->id ?: $request->ip())));
        RateLimiter::for('checkout', fn (Request $request) => Limit::perMinute(10)
            ->by((string) ($request->user()?->id ?: $request->ip())));

        ResetPassword::createUrlUsing(
            fn (User $user, string $token): string => sprintf(
                '%s/reset-password?token=%s&email=%s',
                rtrim((string) config('app.frontend_url'), '/'),
                urlencode($token),
                urlencode($user->getEmailForPasswordReset()),
            ),
        );
    }
}

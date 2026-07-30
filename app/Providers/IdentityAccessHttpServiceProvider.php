<?php

namespace App\Providers;

use App\Application\IdentityAccessHttp\Contract\IdentityAccessHttpRuntime;
use App\Application\IdentityAccessHttp\FailClosedIdentityAccessHttpRuntime;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class IdentityAccessHttpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(IdentityAccessHttpRuntime::class, FailClosedIdentityAccessHttpRuntime::class);
    }

    public function boot(): void
    {
        RateLimiter::for('iam-login', fn (Request $request): Limit => Limit::perMinute(
            (int) config('identity_access_http.rate_limit.login_per_minute'),
        )->by($this->riskKey($request, 'identifier')));

        RateLimiter::for('iam-recovery', fn (Request $request): Limit => Limit::perMinute(
            (int) config('identity_access_http.rate_limit.recovery_per_minute'),
        )->by($this->riskKey($request, 'identifier')));

        RateLimiter::for('iam-authenticated', fn (Request $request): Limit => Limit::perMinute(
            (int) config('identity_access_http.rate_limit.authenticated_per_minute'),
        )->by(hash('sha256', (string) $request->cookie(
            (string) config('identity_access_http.cookie.name'),
            $request->ip(),
        ))));
    }

    private function riskKey(Request $request, string $field): string
    {
        return hash_hmac(
            'sha256',
            mb_strtolower(trim((string) $request->input($field))).'|'.$request->ip(),
            (string) config('identity_access_http.risk_hmac_key'),
        );
    }
}

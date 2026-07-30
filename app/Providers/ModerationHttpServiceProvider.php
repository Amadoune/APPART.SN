<?php

namespace App\Providers;

use App\Application\ModerationHttp\Contract\ModerationHttpRuntimeV1;
use App\Application\ModerationHttp\DeterministicModerationHttpRuntimeV1;
use App\Http\ModerationHttpResponseMapper;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class ModerationHttpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModerationHttpResponseMapper::class);
        $this->app->singleton(DeterministicModerationHttpRuntimeV1::class);
        $this->app->alias(DeterministicModerationHttpRuntimeV1::class, ModerationHttpRuntimeV1::class);
    }

    public function boot(): void
    {
        RateLimiter::for('moderation-http-v1', static fn (Request $request): Limit => Limit::perMinute(60)
            ->by(hash_hmac('sha256', self::riskIdentity($request), (string) config('app.key'))));
    }

    private static function riskIdentity(Request $request): string
    {
        $account = $request->attributes->get('iam_account_id');

        return $account instanceof AccountId ? $account->value : (string) $request->ip();
    }
}

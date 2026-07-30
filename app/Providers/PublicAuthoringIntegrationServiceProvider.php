<?php

namespace App\Providers;

use App\Application\PublicAuthoringIntegration\Contract\PublicAuthoringJourney;
use App\Application\PublicAuthoringIntegration\DeterministicPublicAuthoringJourney;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class PublicAuthoringIntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeterministicPublicAuthoringJourney::class);
        $this->app->alias(DeterministicPublicAuthoringJourney::class, PublicAuthoringJourney::class);
    }

    public function boot(): void
    {
        RateLimiter::for('public-authoring-v1', static fn (Request $request): Limit => Limit::perMinute(60)
            ->by(hash_hmac('sha256', self::scope($request), (string) config('property_listing_authoring_http.risk_hmac_key'))));
    }

    private static function scope(Request $request): string
    {
        $account = $request->attributes->get('iam_account_id');

        return $account instanceof AccountId ? $account->value : (string) $request->ip();
    }
}

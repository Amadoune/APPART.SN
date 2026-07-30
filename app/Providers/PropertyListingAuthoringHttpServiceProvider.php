<?php

namespace App\Providers;

use App\Application\PropertyListingAuthoringHttp\Contract\PropertyListingAuthoringHttpRuntime;
use App\Application\PropertyListingAuthoringHttp\DeterministicPropertyListingAuthoringHttpRuntime;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class PropertyListingAuthoringHttpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeterministicPropertyListingAuthoringHttpRuntime::class);
        $this->app->alias(DeterministicPropertyListingAuthoringHttpRuntime::class, PropertyListingAuthoringHttpRuntime::class);
    }

    public function boot(): void
    {
        RateLimiter::for('property-listing-authoring', static fn (Request $request): Limit => Limit::perMinute(
            (int) config('property_listing_authoring_http.rate_limit_per_minute'),
        )->by(hash_hmac('sha256', self::riskIdentity($request), (string) config('property_listing_authoring_http.risk_hmac_key'))));
    }

    private static function riskIdentity(Request $request): string
    {
        $account = $request->attributes->get('iam_account_id');

        return $account instanceof AccountId ? $account->value : (string) $request->ip();
    }
}

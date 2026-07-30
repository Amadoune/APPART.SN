<?php

namespace App\Providers;

use App\Application\ProfessionalProfileRuntime\Contract\ProfessionalProfileRuntimeAvailabilityPolicy;
use App\Application\ProfessionalProfileRuntime\Contract\ProfessionalProfileRuntimeV1;
use App\Application\ProfessionalProfileRuntime\DeterministicProfessionalProfileRuntimeAvailabilityPolicy;
use App\Application\ProfessionalProfileRuntime\DeterministicProfessionalProfileRuntimeV1;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract\ProfessionalPublicPortfolioStore;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract\ProfessionalPublicProfileStore;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract\ProfessionalVerificationStore;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class ProfessionalProfileRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ProfessionalProfileRuntimeAvailabilityPolicy::class,
            static fn (Application $app): ProfessionalProfileRuntimeAvailabilityPolicy => new DeterministicProfessionalProfileRuntimeAvailabilityPolicy([
                'professional_public_profile' => self::compatible($app, ProfessionalPublicProfileStore::class),
                'professional_verification' => self::compatible($app, ProfessionalVerificationStore::class),
                'professional_public_portfolio' => self::compatible($app, ProfessionalPublicPortfolioStore::class),
            ]),
        );
        $this->app->singleton(DeterministicProfessionalProfileRuntimeV1::class);
        $this->app->alias(DeterministicProfessionalProfileRuntimeV1::class, ProfessionalProfileRuntimeV1::class);
    }

    private static function compatible(Application $app, string $contract): bool
    {
        return $app->bound($contract) && $app->make($contract) instanceof $contract;
    }
}

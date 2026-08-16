<?php

namespace App\Providers;

use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\Contract\AddressIdentityIssuerV1;
use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\DeterministicAddressIdentityIssuerV1;
use Illuminate\Support\ServiceProvider;

final class AddressIdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeterministicAddressIdentityIssuerV1::class);
        $this->app->alias(DeterministicAddressIdentityIssuerV1::class, AddressIdentityIssuerV1::class);
    }
}

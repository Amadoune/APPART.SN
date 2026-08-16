<?php

namespace Tests\Feature;

use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\Contract\AddressIdentityIssuerV1;
use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\DeterministicAddressIdentityIssuerV1;
use Tests\TestCase;

final class AddressIdentityBindingTest extends TestCase
{
    public function test_address_identity_issuer_resolves_to_the_pure_deterministic_implementation(): void
    {
        self::assertInstanceOf(DeterministicAddressIdentityIssuerV1::class, $this->app->make(AddressIdentityIssuerV1::class));
    }
}

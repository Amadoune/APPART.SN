<?php

namespace Appart\Modules\RealEstateCatalog\Application\AddressIdentity\Contract;

use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\AddressIdentityIssuanceResult;
use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\AddressIntentId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;

interface AddressIdentityIssuerV1
{
    public function issue(PropertyId $propertyId, AddressIntentId $addressIntentId): AddressIdentityIssuanceResult;
}

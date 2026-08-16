<?php

namespace Appart\Modules\RealEstateCatalog\Application\AddressIdentity;

use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressId;

final readonly class AddressIdentityIssuanceResult
{
    private function __construct(public AddressIdentityIssuanceStatus $status, public AddressId $addressId) {}

    public static function issued(AddressId $addressId): self
    {
        return new self(AddressIdentityIssuanceStatus::Issued, $addressId);
    }
}

<?php

namespace Appart\Modules\RealEstateCatalog\Application\AddressIdentity;

use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\Contract\AddressIdentityIssuerV1;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use LogicException;

final readonly class DeterministicAddressIdentityIssuerV1 implements AddressIdentityIssuerV1
{
    private const string NAMESPACE_URL = '6ba7b811-9dad-11d1-80b4-00c04fd430c8';

    private const string NAME_PREFIX = 'https://appart.sn/real-estate-catalog/address-intents/v1/';

    public function issue(PropertyId $propertyId, AddressIntentId $addressIntentId): AddressIdentityIssuanceResult
    {
        $name = self::NAME_PREFIX.$propertyId->value.'/'.$addressIntentId->value;

        return AddressIdentityIssuanceResult::issued(AddressId::fromString($this->uuidV5($name)));
    }

    private function uuidV5(string $name): string
    {
        $namespace = hex2bin(str_replace('-', '', self::NAMESPACE_URL));
        if (! is_string($namespace)) {
            throw new LogicException('The certified Address identity namespace is invalid.');
        }

        $bytes = substr(sha1($namespace.$name, true), 0, 16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x50);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20),
        );
    }
}

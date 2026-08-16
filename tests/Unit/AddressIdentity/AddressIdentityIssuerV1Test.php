<?php

namespace Tests\Unit\AddressIdentity;

use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\AddressIdentityIssuanceStatus;
use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\AddressIntentId;
use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\DeterministicAddressIdentityIssuerV1;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class AddressIdentityIssuerV1Test extends TestCase
{
    private const string PROPERTY_ID = '10000000-0000-4000-8000-000000000001';

    private const string INTENT_ID = '20000000-0000-4000-8000-000000000001';

    private const string EXPECTED_ADDRESS_ID = 'fe428218-c951-58ce-9bac-098eba707ef9';

    public function test_certified_uuid_v5_vector_is_exact_and_accepted_by_address_id(): void
    {
        $result = (new DeterministicAddressIdentityIssuerV1)->issue(
            PropertyId::fromString(self::PROPERTY_ID),
            AddressIntentId::fromString(self::INTENT_ID),
        );

        self::assertSame(AddressIdentityIssuanceStatus::Issued, $result->status);
        self::assertSame(self::EXPECTED_ADDRESS_ID, $result->addressId->value);
        self::assertSame(self::EXPECTED_ADDRESS_ID, AddressId::fromString($result->addressId->value)->value);
        self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $result->addressId->value);
    }

    public function test_same_intention_is_stable_across_calls_instances_and_reconstruction(): void
    {
        $first = (new DeterministicAddressIdentityIssuerV1)->issue(PropertyId::fromString(self::PROPERTY_ID), AddressIntentId::fromString(self::INTENT_ID));
        $second = (new DeterministicAddressIdentityIssuerV1)->issue(PropertyId::fromString(strtoupper(self::PROPERTY_ID)), AddressIntentId::fromString(strtoupper(self::INTENT_ID)));

        self::assertTrue($first->addressId->equals($second->addressId));
        self::assertSame(self::EXPECTED_ADDRESS_ID, $second->addressId->value);
    }

    public function test_new_intention_and_other_property_produce_distinct_identifiers(): void
    {
        $issuer = new DeterministicAddressIdentityIssuerV1;
        $baseline = $issuer->issue(PropertyId::fromString(self::PROPERTY_ID), AddressIntentId::fromString(self::INTENT_ID));
        $newIntent = $issuer->issue(PropertyId::fromString(self::PROPERTY_ID), AddressIntentId::fromString('20000000-0000-4000-8000-000000000002'));
        $otherProperty = $issuer->issue(PropertyId::fromString('10000000-0000-4000-8000-000000000002'), AddressIntentId::fromString(self::INTENT_ID));

        self::assertFalse($baseline->addressId->equals($newIntent->addressId));
        self::assertFalse($baseline->addressId->equals($otherProperty->addressId));
        self::assertFalse($newIntent->addressId->equals($otherProperty->addressId));
    }

    public function test_address_intent_id_rejects_non_uuid_input(): void
    {
        $this->expectException(InvalidArgumentException::class);
        AddressIntentId::fromString('client-address-id');
    }
}

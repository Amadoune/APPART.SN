<?php

namespace Tests\Unit\MediaOwnership;

use Appart\Modules\Media\Domain\Exception\InvalidMediaValue;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;
use PHPUnit\Framework\TestCase;

final class MediaOwnershipIdentityTest extends TestCase
{
    public function test_identities_are_explicit_and_normalized(): void
    {
        self::assertSame('99000000-0000-4000-8000-000000000001', PropertyId::fromString(' 99000000-0000-4000-8000-000000000001 ')->value);
        self::assertSame('99100000-0000-4000-8000-000000000001', MediaCollectionId::fromString('99100000-0000-4000-8000-000000000001')->value);
    }

    public function test_identity_cannot_be_inferred_from_a_naming_convention(): void
    {
        $this->expectException(InvalidMediaValue::class);
        PropertyId::fromString('property:listing-derived');
    }
}

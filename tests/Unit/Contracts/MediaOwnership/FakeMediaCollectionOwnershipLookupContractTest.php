<?php

namespace Tests\Unit\Contracts\MediaOwnership;

use Appart\Modules\Media\Application\Contract\MediaCollectionOwnershipLookup;
use Appart\Modules\Media\Application\Ownership\MediaOwnershipResult;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;

final class FakeMediaCollectionOwnershipLookupContractTest extends MediaCollectionOwnershipLookupContract
{
    private FakeMediaCollectionOwnershipLookup $fake;

    protected function setUp(): void
    {
        $this->fake = new FakeMediaCollectionOwnershipLookup;
    }

    protected function lookup(): MediaCollectionOwnershipLookup
    {
        return $this->fake;
    }

    protected function own(PropertyId $propertyId, MediaCollectionId $collectionId): void
    {
        $this->fake->ownership[$propertyId->value][] = $collectionId;
    }
}

final class FakeMediaCollectionOwnershipLookup implements MediaCollectionOwnershipLookup
{
    /** @var array<string, list<MediaCollectionId>> */
    public array $ownership = [];

    public function resolve(PropertyId $propertyId): MediaOwnershipResult
    {
        $ids = $this->ownership[$propertyId->value] ?? [];
        if ($ids === []) {
            return MediaOwnershipResult::missing($propertyId);
        }
        if (count($ids) === 1) {
            return MediaOwnershipResult::found($propertyId, $ids[0]);
        }

        return MediaOwnershipResult::ambiguous($propertyId);
    }
}

<?php

namespace Tests\Unit\Contracts\MediaOwnership;

use Appart\Modules\Media\Application\Contract\MediaCollectionOwnershipLookup;
use Appart\Modules\Media\Application\Ownership\MediaOwnershipResolution;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;
use PHPUnit\Framework\TestCase;

abstract class MediaCollectionOwnershipLookupContract extends TestCase
{
    abstract protected function lookup(): MediaCollectionOwnershipLookup;

    abstract protected function own(PropertyId $propertyId, MediaCollectionId $collectionId): void;

    public function test_missing_is_explicit_and_contains_no_collection_identity(): void
    {
        $property = $this->property(1);
        $result = $this->lookup()->resolve($property);

        self::assertSame(MediaOwnershipResolution::Missing, $result->resolution);
        self::assertSame($property, $result->propertyId);
        self::assertNull($result->collectionId);
    }

    public function test_one_owned_collection_is_found_exactly(): void
    {
        $property = $this->property(1);
        $collection = $this->collection(1);
        $this->own($property, $collection);

        $result = $this->lookup()->resolve($property);

        self::assertSame(MediaOwnershipResolution::Found, $result->resolution);
        self::assertSame($collection->value, $result->collectionId?->value);
    }

    public function test_multiple_owned_collections_are_ambiguous_without_implicit_selection(): void
    {
        $property = $this->property(1);
        $this->own($property, $this->collection(1));
        $this->own($property, $this->collection(2));

        $result = $this->lookup()->resolve($property);

        self::assertSame(MediaOwnershipResolution::Ambiguous, $result->resolution);
        self::assertNull($result->collectionId);
    }

    public function test_collections_owned_by_other_properties_never_leak_into_resolution(): void
    {
        $this->own($this->property(2), $this->collection(1));

        self::assertSame(MediaOwnershipResolution::Missing, $this->lookup()->resolve($this->property(1))->resolution);
    }

    protected function property(int $suffix): PropertyId
    {
        return PropertyId::fromString(sprintf('99000000-0000-4000-8000-%012d', $suffix));
    }

    protected function collection(int $suffix): MediaCollectionId
    {
        return MediaCollectionId::fromString(sprintf('99100000-0000-4000-8000-%012d', $suffix));
    }
}

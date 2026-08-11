<?php

namespace Tests\Unit\MediaPropertyAuthoringCatalogAdapter;

use App\Infrastructure\MediaAttachment\CompositeMediaPropertyCatalog;
use App\Infrastructure\MediaAttachment\PropertyAuthoringMediaCatalogAdapter;
use App\Infrastructure\MediaAttachment\RegistryMediaPropertyCatalog;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\Contract\PropertyAuthoringStore;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringPersistenceWriteResult;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;
use Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId as CatalogPropertyId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MediaPropertyAuthoringCatalogAdapterTest extends TestCase
{
    #[Test]
    public function owner_scoped_authoring_property_is_resolved_without_accessing_the_aggregate_registry(): void
    {
        $propertyId = $this->id(1);
        $store = new CatalogAuthoringStore(new PropertyAuthoringState(
            $propertyId,
            $this->id(2),
            1,
            $this->id(3),
            hash('sha256', 'authoring'),
            'apartment',
            'Dakar',
            'Almadies',
        ));
        $registry = new CountingPropertyRegistry;
        $catalog = new CompositeMediaPropertyCatalog(
            new PropertyAuthoringMediaCatalogAdapter($store),
            new RegistryMediaPropertyCatalog($registry),
        );

        self::assertTrue($catalog->exists(PropertyId::fromString($propertyId)));
        self::assertSame(1, $store->reads);
        self::assertSame(0, $registry->reads);
    }

    #[Test]
    public function missing_or_non_owner_scoped_authoring_state_is_fail_closed(): void
    {
        $propertyId = $this->id(10);
        self::assertFalse((new PropertyAuthoringMediaCatalogAdapter(new CatalogAuthoringStore(null)))->exists(PropertyId::fromString($propertyId)));
        self::assertFalse((new PropertyAuthoringMediaCatalogAdapter(new CatalogAuthoringStore(new PropertyAuthoringState(
            $propertyId,
            '',
            1,
            $this->id(11),
            hash('sha256', 'invalid-owner'),
        ))))->exists(PropertyId::fromString($propertyId)));
    }

    private function id(int $suffix): string
    {
        return sprintf('68000000-0000-4000-8000-%012d', $suffix);
    }
}

final class CatalogAuthoringStore implements PropertyAuthoringStore
{
    public int $reads = 0;

    public function __construct(private readonly ?PropertyAuthoringState $state) {}

    public function read(string $propertyId): ?PropertyAuthoringState
    {
        $this->reads++;

        return $this->state?->propertyId === $propertyId ? $this->state : null;
    }

    public function save(PropertyAuthoringState $state, int $expectedVersion): PropertyAuthoringPersistenceWriteResult
    {
        return PropertyAuthoringPersistenceWriteResult::Rejected;
    }
}

final class CountingPropertyRegistry implements PropertyRegistry
{
    public int $reads = 0;

    public function find(CatalogPropertyId $id): ?Property
    {
        $this->reads++;

        return null;
    }

    public function add(Property $property): void {}

    public function save(Property $property, int $expectedVersion): void {}
}

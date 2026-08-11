<?php

namespace Tests\Unit\PropertyAuthoringPublicSurface;

use App\Application\PropertyListingAuthoringHttp\DeterministicPropertyListingAuthoringHttpRuntime;
use App\Application\PropertyListingAuthoringHttp\PropertyListingAuthoringHttpOperation;
use App\Application\PropertyListingAuthoringHttp\PropertyListingAuthoringHttpStatus;
use App\Application\PropertyListingAuthoringRuntime\Contract\PropertyListingAuthoringRuntimeReport;
use App\Application\PropertyListingAuthoringRuntime\Contract\PropertyListingAuthoringRuntimeV1;
use App\Application\PropertyListingAuthoringRuntime\PropertyListingAuthoringRuntimeStatus;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\Contract\PropertyAuthoringStore;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringPersistenceWriteResult;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DeterministicPropertyAuthoringPublicSurfaceTest extends TestCase
{
    #[Test]
    public function owner_can_write_then_reload_the_three_property_fields(): void
    {
        $store = new InMemoryPropertyAuthoringStore;
        $runtime = $this->createMock(PropertyListingAuthoringRuntimeV1::class);
        $runtime->method('inspect')->willReturn(new PropertyListingAuthoringRuntimeReport(PropertyListingAuthoringRuntimeStatus::Ready));
        $runtime->method('propertyAuthoring')->willReturn($store);
        $surface = new DeterministicPropertyListingAuthoringHttpRuntime($runtime);

        $write = $surface->execute(
            PropertyListingAuthoringHttpOperation::InitiateProperty,
            '94000000-0000-4000-8000-000000000002',
            '94000000-0000-4000-8000-000000000001',
            '94000000-0000-4000-8000-000000000003',
            ['propertyType' => 'apartment', 'city' => 'Dakar', 'neighborhood' => 'Almadies'],
        );
        $reloaded = $surface->execute(
            PropertyListingAuthoringHttpOperation::ReadProperty,
            '94000000-0000-4000-8000-000000000002',
            '94000000-0000-4000-8000-000000000001',
            null,
            [],
        );

        self::assertSame(PropertyListingAuthoringHttpStatus::Succeeded, $write->status);
        self::assertSame(PropertyListingAuthoringHttpStatus::Succeeded, $reloaded->status);
        self::assertSame('apartment', $reloaded->data['propertyType']);
        self::assertSame('Dakar', $reloaded->data['city']);
        self::assertSame('Almadies', $reloaded->data['neighborhood']);
    }
}

final class InMemoryPropertyAuthoringStore implements PropertyAuthoringStore
{
    private ?PropertyAuthoringState $state = null;

    public function read(string $propertyId): ?PropertyAuthoringState
    {
        return $this->state?->propertyId === $propertyId ? $this->state : null;
    }

    public function save(PropertyAuthoringState $state, int $expectedVersion): PropertyAuthoringPersistenceWriteResult
    {
        if (($this->state === null ? 0 : $this->state->version) !== $expectedVersion) {
            return PropertyAuthoringPersistenceWriteResult::VersionConflict;
        }
        $this->state = $state;

        return PropertyAuthoringPersistenceWriteResult::Applied;
    }
}

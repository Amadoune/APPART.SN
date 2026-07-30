<?php

namespace Tests\Unit\Contracts\RealEstateCatalog;

use Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry;
use Appart\Modules\RealEstateCatalog\Domain\Exception\ConcurrentPropertyModification;
use Appart\Modules\RealEstateCatalog\Domain\Exception\PropertyIdConflict;
use Appart\Modules\RealEstateCatalog\Domain\Exception\PropertyReferenceConflict;
use Appart\Modules\RealEstateCatalog\Domain\Exception\PropertyViolation;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyStatus;
use PHPUnit\Framework\TestCase;

abstract class PropertyRegistryContract extends TestCase
{
    private PropertyRegistryHarness $harness;

    private PropertyRegistry $registry;

    final protected function setUp(): void
    {
        $this->harness = $this->createHarness();
        $this->registry = $this->harness->freshRegistry();
    }

    abstract protected function createHarness(): PropertyRegistryHarness;

    final public function test_unknown_identity_returns_explicit_absence(): void
    {
        self::assertNull($this->registry->find($this->harness->primaryId()));
    }

    final public function test_add_then_find_restores_every_observable_property(): void
    {
        $expected = $this->harness->propertyWithHistory();
        $this->registry->add($expected);
        $actual = $this->requiredFind($expected);

        self::assertSame($expected->id()->value, $actual->id()->value);
        self::assertSame($expected->reference()->value, $actual->reference()->value);
        self::assertSame($expected->type(), $actual->type());
        self::assertEquals($expected->surface(), $actual->surface());
        self::assertEquals($expected->rooms(), $actual->rooms());
        self::assertEquals($expected->bathrooms(), $actual->bathrooms());
        self::assertEquals($expected->constructionYear(), $actual->constructionYear());
        self::assertEquals($expected->address(), $actual->address());
        self::assertSame($expected->status(), $actual->status());
        self::assertSame($expected->version(), $actual->version());
    }

    final public function test_reads_are_detached_independent_and_invisible_without_save(): void
    {
        $property = $this->harness->minimalProperty();
        $this->registry->add($property);
        $first = $this->requiredFind($property);
        $second = $this->requiredFind($property);
        $this->harness->mutate($first);

        self::assertNotSame($first, $second);
        self::assertSame(0, $second->version());
        self::assertSame(0, $this->requiredFind($property)->version());
    }

    final public function test_reloaded_property_has_no_residual_or_replayed_events(): void
    {
        $property = $this->harness->propertyWithHistory();
        $this->registry->add($property);

        self::assertSame([], $this->requiredFind($property)->releaseEvents());
        self::assertSame([], $this->requiredFind($property)->releaseEvents());
    }

    final public function test_successful_add_and_save_preserve_caller_events(): void
    {
        $property = $this->harness->minimalProperty();
        $this->registry->add($property);
        self::assertNotEmpty($property->releaseEvents());
        $loaded = $this->requiredFind($property);
        $this->harness->mutate($loaded);
        $this->registry->save($loaded, 0);
        self::assertNotEmpty($loaded->releaseEvents());
    }

    final public function test_add_distinguishes_identity_and_reference_conflicts(): void
    {
        $this->registry->add($this->harness->minimalProperty());
        try {
            $this->registry->add($this->harness->minimalProperty());
            self::fail('Duplicate PropertyId must fail.');
        } catch (PropertyIdConflict) {
            self::assertSame(0, $this->requiredFind($this->harness->minimalProperty())->version());
        }

        $this->expectException(PropertyReferenceConflict::class);
        $this->registry->add($this->harness->minimalProperty($this->harness->distinctId(), $this->harness->primaryReference()));
    }

    final public function test_identity_and_reference_reservations_survive_archival(): void
    {
        $archived = $this->harness->archivedProperty();
        $this->registry->add($archived);

        try {
            $this->registry->add($this->harness->minimalProperty());
            self::fail('Archived PropertyId remains reserved.');
        } catch (PropertyIdConflict) {
            self::assertSame(PropertyStatus::Archived, $this->requiredFind($archived)->status());
        }

        $this->expectException(PropertyReferenceConflict::class);
        $this->registry->add($this->harness->minimalProperty($this->harness->distinctId(), $this->harness->primaryReference()));
    }

    final public function test_save_with_exact_expected_version_persists_the_candidate_version(): void
    {
        $property = $this->harness->minimalProperty();
        $this->registry->add($property);
        $loaded = $this->requiredFind($property);
        $this->harness->mutate($loaded);
        $this->registry->save($loaded, 0);

        self::assertSame($loaded->version(), $this->requiredFind($property)->version());
        self::assertEquals($loaded->surface(), $this->requiredFind($property)->surface());
    }

    final public function test_stale_version_and_absent_root_use_concurrency_conflict(): void
    {
        $property = $this->harness->minimalProperty();
        $this->registry->add($property);
        $winner = $this->requiredFind($property);
        $stale = $this->requiredFind($property);
        $this->harness->mutate($winner);
        $this->harness->mutate($stale);
        $this->registry->save($winner, 0);
        try {
            $this->registry->save($stale, 0);
            self::fail('Stale save must fail.');
        } catch (ConcurrentPropertyModification) {
            $absent = $this->harness->minimalProperty($this->harness->distinctId(), $this->harness->distinctReference());
            $this->harness->mutate($absent);
            $this->expectException(ConcurrentPropertyModification::class);
            $this->registry->save($absent, 0);
        }
    }

    final public function test_registry_never_invents_or_increments_a_version(): void
    {
        $property = $this->harness->minimalProperty();
        $this->registry->add($property);
        self::assertSame(0, $this->requiredFind($property)->version());
        $this->harness->mutate($property);
        $this->registry->save($property, 0);
        self::assertSame(1, $this->requiredFind($property)->version());
    }

    final public function test_failed_save_rolls_back_visibility_and_preserves_events(): void
    {
        $property = $this->harness->minimalProperty();
        $this->registry->add($property);
        $loaded = $this->requiredFind($property);
        $this->harness->mutate($loaded);
        $this->harness->failNextWrite($this->registry);
        try {
            $this->registry->save($loaded, 0);
            self::fail('Controlled failure must be visible.');
        } catch (ConcurrentPropertyModification) {
            self::assertSame(0, $this->requiredFind($property)->version());
            self::assertNotEmpty($loaded->releaseEvents());
        }
    }

    final public function test_address_identity_and_value_are_preserved(): void
    {
        $property = $this->harness->propertyWithHistory();
        $this->registry->add($property);
        $actual = $this->requiredFind($property)->address();

        self::assertNotNull($actual);
        self::assertSame($property->address()?->id->value, $actual->id->value);
        self::assertSame($property->address()?->placeId->value, $actual->placeId->value);
        self::assertSame($property->address()?->line->value, $actual->line->value);
    }

    final public function test_archived_state_is_reconstructed_and_remains_terminal(): void
    {
        $property = $this->harness->archivedProperty();
        $this->registry->add($property);
        $loaded = $this->requiredFind($property);
        self::assertSame(PropertyStatus::Archived, $loaded->status());

        $this->expectException(PropertyViolation::class);
        $this->harness->mutate($loaded);
    }

    final public function test_each_scenario_starts_with_an_isolated_empty_registry(): void
    {
        self::assertNull($this->registry->find($this->harness->primaryId()));
        self::assertNull($this->registry->find($this->harness->distinctId()));
    }

    private function requiredFind(Property $property): Property
    {
        $loaded = $this->registry->find($property->id());
        self::assertNotNull($loaded);

        return $loaded;
    }
}

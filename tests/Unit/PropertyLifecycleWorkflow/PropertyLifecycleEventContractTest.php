<?php

namespace Tests\Unit\PropertyLifecycleWorkflow;

use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEvent;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventCatalog;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventId;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventInstant;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventMetadata;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventPayload;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventPayloadVersion;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventSerializer;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventType;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\UnsupportedPropertyLifecycleEventTransition;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleAction;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleState;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleTransition;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PropertyLifecycleEventContractTest extends TestCase
{
    /** @return iterable<string, array{PropertyLifecycleState,PropertyLifecycleAction,PropertyLifecycleState,PropertyLifecycleEventType}> */
    public static function transitionEvents(): iterable
    {
        $matrix = [
            'draft>activate>active' => PropertyLifecycleEventType::PropertyActivated,
            'draft>archive>archived' => PropertyLifecycleEventType::PropertyArchived,
            'active>begin_maintenance>under_maintenance' => PropertyLifecycleEventType::PropertyMaintenanceStarted,
            'active>mark_unavailable>unavailable' => PropertyLifecycleEventType::PropertyMarkedUnavailable,
            'active>decommission>decommissioned' => PropertyLifecycleEventType::PropertyDecommissioned,
            'under_maintenance>complete_maintenance>active' => PropertyLifecycleEventType::PropertyMaintenanceCompleted,
            'under_maintenance>mark_unavailable>unavailable' => PropertyLifecycleEventType::PropertyMarkedUnavailable,
            'under_maintenance>decommission>decommissioned' => PropertyLifecycleEventType::PropertyDecommissioned,
            'unavailable>restore_availability>active' => PropertyLifecycleEventType::PropertyAvailabilityRestored,
            'unavailable>begin_maintenance>under_maintenance' => PropertyLifecycleEventType::PropertyMaintenanceStarted,
            'unavailable>decommission>decommissioned' => PropertyLifecycleEventType::PropertyDecommissioned,
            'decommissioned>archive>archived' => PropertyLifecycleEventType::PropertyArchived,
        ];
        foreach ($matrix as $transition => $type) {
            [$from, $action, $to] = explode('>', $transition);
            yield $transition => [PropertyLifecycleState::from($from), PropertyLifecycleAction::from($action), PropertyLifecycleState::from($to), $type];
        }
    }

    #[DataProvider('transitionEvents')]
    public function test_each_certified_transition_produces_exactly_one_event(PropertyLifecycleState $from, PropertyLifecycleAction $action, PropertyLifecycleState $to, PropertyLifecycleEventType $expected): void
    {
        $events = (new PropertyLifecycleEventCatalog)->eventsFor($this->propertyId(), new PropertyLifecycleTransition($from, $to, $action), 2, $this->metadata());

        self::assertCount(1, $events);
        self::assertSame($expected, $events[0]->type);
        self::assertSame(1, $events[0]->payloadVersion->value);
        self::assertSame([$this->propertyId()->value, $from->value, $to->value, $action->value, 2], array_values($events[0]->payload->fields()));
    }

    public function test_catalog_is_exactly_twelve_mappings_and_rejects_an_uncertified_transition(): void
    {
        self::assertSame(12, (new PropertyLifecycleEventCatalog)->transitionCount());
        $this->expectException(UnsupportedPropertyLifecycleEventTransition::class);
        (new PropertyLifecycleEventCatalog)->eventsFor(
            $this->propertyId(),
            new PropertyLifecycleTransition(PropertyLifecycleState::Draft, PropertyLifecycleState::Decommissioned, PropertyLifecycleAction::Decommission),
            2,
            $this->metadata(),
        );
    }

    public function test_identity_and_serialization_are_stable_on_replay(): void
    {
        $transition = new PropertyLifecycleTransition(PropertyLifecycleState::Draft, PropertyLifecycleState::Active, PropertyLifecycleAction::Activate);
        $catalog = new PropertyLifecycleEventCatalog;
        $first = $catalog->eventsFor($this->propertyId(), $transition, 2, $this->metadata())[0];
        $second = $catalog->eventsFor($this->propertyId(), $transition, 2, $this->metadata())[0];

        self::assertEquals($first, $second);
        self::assertSame((new PropertyLifecycleEventSerializer)->serialize($first), (new PropertyLifecycleEventSerializer)->serialize($second));
        self::assertSame('2026-07-21T10:00:00.000000Z', $first->metadata->occurredAt->value);
        self::assertSame('2026-07-21T10:00:01.000000Z', $first->metadata->recordedAt->value);
    }

    public function test_identity_changes_with_property_version_or_event_type(): void
    {
        $payload = $this->payload($this->propertyId(), 2);
        $base = PropertyLifecycleEventId::derive(PropertyLifecycleEventType::PropertyActivated, PropertyLifecycleEventPayloadVersion::V1, $payload);
        $otherProperty = PropertyLifecycleEventId::derive(PropertyLifecycleEventType::PropertyActivated, PropertyLifecycleEventPayloadVersion::V1, $this->payload(PropertyId::fromString('33333333-3333-4333-8333-333333333333'), 2));
        $otherVersion = PropertyLifecycleEventId::derive(PropertyLifecycleEventType::PropertyActivated, PropertyLifecycleEventPayloadVersion::V1, $this->payload($this->propertyId(), 3));
        $otherType = PropertyLifecycleEventId::derive(PropertyLifecycleEventType::PropertyArchived, PropertyLifecycleEventPayloadVersion::V1, $payload);

        self::assertNotSame($base->value, $otherProperty->value);
        self::assertNotSame($base->value, $otherVersion->value);
        self::assertNotSame($base->value, $otherType->value);
    }

    public function test_canonical_serialization_has_normative_root_payload_and_metadata_order(): void
    {
        $event = (new PropertyLifecycleEventCatalog)->eventsFor(
            $this->propertyId(),
            new PropertyLifecycleTransition(PropertyLifecycleState::Draft, PropertyLifecycleState::Active, PropertyLifecycleAction::Activate),
            2,
            $this->metadata(),
        )[0];
        $json = (new PropertyLifecycleEventSerializer)->serialize($event);
        $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        self::assertSame(['eventId', 'eventType', 'payloadVersion', 'payload', 'metadata'], array_keys($decoded));
        self::assertSame(['propertyId', 'previousState', 'state', 'action', 'lifecycleVersion'], array_keys($decoded['payload']));
        self::assertSame(['occurredAt', 'recordedAt'], array_keys($decoded['metadata']));
        self::assertSame($json, (new PropertyLifecycleEventSerializer)->serialize($event));
    }

    public function test_contract_is_closed_versioned_and_immutable(): void
    {
        self::assertCount(7, PropertyLifecycleEventType::cases());
        self::assertSame([PropertyLifecycleEventPayloadVersion::V1], PropertyLifecycleEventPayloadVersion::cases());
        foreach ([PropertyLifecycleEvent::class, PropertyLifecycleEventId::class, PropertyLifecycleEventInstant::class, PropertyLifecycleEventMetadata::class, PropertyLifecycleEventPayload::class, PropertyLifecycleEventCatalog::class, PropertyLifecycleEventSerializer::class] as $class) {
            $reflection = new ReflectionClass($class);
            self::assertTrue($reflection->isFinal(), $class);
            self::assertTrue($reflection->isReadOnly(), $class);
        }
    }

    public function test_transition_version_must_be_at_least_two(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->payload($this->propertyId(), 1);
    }

    public function test_metadata_must_be_canonical_explicit_and_causally_ordered(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PropertyLifecycleEventMetadata(
            PropertyLifecycleEventInstant::fromCanonicalUtc('2026-07-21T10:00:01.000000Z'),
            PropertyLifecycleEventInstant::fromCanonicalUtc('2026-07-21T10:00:00.000000Z'),
        );
    }

    public function test_non_canonical_instant_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PropertyLifecycleEventInstant::fromCanonicalUtc('2026-07-21 10:00:00');
    }

    public function test_event_rejects_an_identity_derived_from_different_semantics(): void
    {
        $payload = $this->payload($this->propertyId(), 2);
        $this->expectException(InvalidArgumentException::class);
        new PropertyLifecycleEvent(
            PropertyLifecycleEventId::derive(PropertyLifecycleEventType::PropertyArchived, PropertyLifecycleEventPayloadVersion::V1, $payload),
            PropertyLifecycleEventType::PropertyActivated,
            PropertyLifecycleEventPayloadVersion::V1,
            $payload,
            $this->metadata(),
        );
    }

    private function propertyId(): PropertyId
    {
        return PropertyId::fromString('22222222-2222-4222-8222-222222222222');
    }

    private function payload(PropertyId $propertyId, int $version): PropertyLifecycleEventPayload
    {
        return new PropertyLifecycleEventPayload($propertyId, PropertyLifecycleState::Draft, PropertyLifecycleState::Active, PropertyLifecycleAction::Activate, $version);
    }

    private function metadata(): PropertyLifecycleEventMetadata
    {
        return new PropertyLifecycleEventMetadata(
            PropertyLifecycleEventInstant::fromCanonicalUtc('2026-07-21T10:00:00.000000Z'),
            PropertyLifecycleEventInstant::fromCanonicalUtc('2026-07-21T10:00:01.000000Z'),
        );
    }
}

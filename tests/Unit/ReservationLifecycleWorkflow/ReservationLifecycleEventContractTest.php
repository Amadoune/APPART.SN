<?php

namespace Tests\Unit\ReservationLifecycleWorkflow;

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEvent;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventAggregateType;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventCatalog;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventId;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventMetadata;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventPayload;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventPayloadVersion;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventSerializer;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventType;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\UnsupportedReservationLifecycleEventTransition;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ReservationLifecycleEventContractTest extends TestCase
{
    /** @return iterable<string, array{ReservationLifecycleState,ReservationLifecycleAction,ReservationLifecycleState,ReservationLifecycleEventType}> */
    public static function transitionEvents(): iterable
    {
        $matrix = [
            'draft>submit>requested' => ReservationLifecycleEventType::ReservationSubmitted,
            'draft>cancel>cancelled' => ReservationLifecycleEventType::ReservationCancelledFromDraft,
            'requested>confirm>confirmed' => ReservationLifecycleEventType::ReservationConfirmed,
            'requested>reject>rejected' => ReservationLifecycleEventType::ReservationRejected,
            'requested>cancel>cancelled' => ReservationLifecycleEventType::ReservationCancelledFromRequested,
            'requested>expire>expired' => ReservationLifecycleEventType::ReservationExpiredFromRequested,
            'confirmed>start>in_progress' => ReservationLifecycleEventType::ReservationStarted,
            'confirmed>cancel>cancelled' => ReservationLifecycleEventType::ReservationCancelledFromConfirmed,
            'confirmed>expire>expired' => ReservationLifecycleEventType::ReservationExpiredFromConfirmed,
            'in_progress>complete>completed' => ReservationLifecycleEventType::ReservationCompleted,
            'in_progress>cancel>cancelled' => ReservationLifecycleEventType::ReservationCancelledInProgress,
        ];

        foreach ($matrix as $transition => $eventType) {
            [$from, $action, $to] = explode('>', $transition);
            yield $transition => [ReservationLifecycleState::from($from), ReservationLifecycleAction::from($action), ReservationLifecycleState::from($to), $eventType];
        }
    }

    #[DataProvider('transitionEvents')]
    public function test_each_certified_transition_maps_to_its_unique_event(ReservationLifecycleState $from, ReservationLifecycleAction $action, ReservationLifecycleState $to, ReservationLifecycleEventType $expectedType): void
    {
        $event = (new ReservationLifecycleEventCatalog)->eventFor($this->reservationId(), new ReservationLifecycleTransition($from, $to, $action), 2);

        self::assertSame($expectedType, $event->metadata->eventType);
        self::assertSame(ReservationLifecycleEventPayloadVersion::V1, $event->metadata->payloadVersion);
        self::assertSame('ReservationLifecycle', $event->payload->aggregateType->value);
        self::assertSame(implode('>', [$from->value, $action->value, $to->value]), $event->payload->transition);
        self::assertSame([$from, $to, $action], [$event->payload->previousState, $event->payload->currentState, $event->payload->action]);
    }

    public function test_catalog_is_closed_bijective_and_exactly_eleven_mappings(): void
    {
        $types = [];
        foreach (self::transitionEvents() as [$from, $action, $to, $type]) {
            $types[] = $type->value;
            (new ReservationLifecycleEventCatalog)->eventFor($this->reservationId(), new ReservationLifecycleTransition($from, $to, $action), 2);
        }

        self::assertSame(11, (new ReservationLifecycleEventCatalog)->transitionCount());
        self::assertCount(11, ReservationLifecycleEventType::cases());
        self::assertCount(11, array_unique($types));
        self::assertEqualsCanonicalizing(array_column(ReservationLifecycleEventType::cases(), 'value'), $types);
    }

    public function test_uncertified_transition_is_refused_explicitly(): void
    {
        $this->expectException(UnsupportedReservationLifecycleEventTransition::class);
        (new ReservationLifecycleEventCatalog)->eventFor(
            $this->reservationId(),
            new ReservationLifecycleTransition(ReservationLifecycleState::Draft, ReservationLifecycleState::Completed, ReservationLifecycleAction::Complete),
            2,
        );
    }

    public function test_payload_v1_contains_only_the_nine_normative_business_fields(): void
    {
        $payload = $this->submittedEvent()->payload;

        self::assertSame(
            ['eventId', 'aggregateType', 'reservationId', 'transition', 'previousState', 'currentState', 'action', 'version', 'occurredVersion'],
            array_keys($payload->fields()),
        );
        self::assertSame(1, $payload->version);
        self::assertSame(2, $payload->occurredVersion);
        self::assertSame($this->reservationId()->value, $payload->reservationId->value);
    }

    public function test_canonical_serialization_has_a_fixed_eleven_field_order(): void
    {
        $event = $this->submittedEvent();
        $serializer = new ReservationLifecycleEventSerializer;
        $json = $serializer->serialize($event);
        $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        self::assertSame(
            ['eventId', 'aggregateType', 'eventType', 'payloadVersion', 'reservationId', 'transition', 'previousState', 'currentState', 'action', 'version', 'occurredVersion'],
            array_keys($decoded),
        );
        self::assertSame($event->payload->eventId->value, $decoded['eventId']);
        self::assertSame('reservation.lifecycle.submitted', $decoded['eventType']);
        self::assertSame($json, $serializer->serialize($event));
    }

    public function test_identity_and_bytes_are_stable_on_replay(): void
    {
        $first = $this->submittedEvent();
        $second = $this->submittedEvent();
        $serializer = new ReservationLifecycleEventSerializer;

        self::assertEquals($first, $second);
        self::assertSame($first->payload->eventId->value, $second->payload->eventId->value);
        self::assertSame($serializer->serialize($first), $serializer->serialize($second));
        self::assertMatchesRegularExpression('/^reservation-lifecycle-[0-9a-f]{64}$/', $first->payload->eventId->value);
    }

    public function test_identity_changes_with_reservation_transition_or_occurred_version(): void
    {
        $catalog = new ReservationLifecycleEventCatalog;
        $transition = new ReservationLifecycleTransition(ReservationLifecycleState::Draft, ReservationLifecycleState::Requested, ReservationLifecycleAction::Submit);
        $base = $catalog->eventFor($this->reservationId(), $transition, 2)->payload->eventId->value;
        $otherReservation = $catalog->eventFor(ReservationId::fromString('33333333-3333-4333-8333-333333333333'), $transition, 2)->payload->eventId->value;
        $otherTransition = $catalog->eventFor($this->reservationId(), new ReservationLifecycleTransition(ReservationLifecycleState::Draft, ReservationLifecycleState::Cancelled, ReservationLifecycleAction::Cancel), 2)->payload->eventId->value;
        $otherVersion = $catalog->eventFor($this->reservationId(), $transition, 3)->payload->eventId->value;

        self::assertCount(4, array_unique([$base, $otherReservation, $otherTransition, $otherVersion]));
    }

    public function test_contract_is_v1_and_all_models_are_immutable(): void
    {
        self::assertSame([ReservationLifecycleEventPayloadVersion::V1], ReservationLifecycleEventPayloadVersion::cases());
        self::assertSame([ReservationLifecycleEventAggregateType::ReservationLifecycle], ReservationLifecycleEventAggregateType::cases());
        foreach ([ReservationLifecycleEvent::class, ReservationLifecycleEventId::class, ReservationLifecycleEventMetadata::class, ReservationLifecycleEventPayload::class, ReservationLifecycleEventCatalog::class, ReservationLifecycleEventSerializer::class] as $class) {
            $reflection = new ReflectionClass($class);
            self::assertTrue($reflection->isFinal(), $class);
            self::assertTrue($reflection->isReadOnly(), $class);
        }
    }

    public function test_non_positive_occurred_version_is_refused(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ReservationLifecycleEventCatalog)->eventFor(
            $this->reservationId(),
            new ReservationLifecycleTransition(ReservationLifecycleState::Draft, ReservationLifecycleState::Requested, ReservationLifecycleAction::Submit),
            0,
        );
    }

    public function test_inconsistent_transition_representation_is_refused(): void
    {
        $transition = new ReservationLifecycleTransition(ReservationLifecycleState::Draft, ReservationLifecycleState::Requested, ReservationLifecycleAction::Submit);
        $this->expectException(InvalidArgumentException::class);
        new ReservationLifecycleEventPayload(
            ReservationLifecycleEventId::derive(ReservationLifecycleEventType::ReservationSubmitted, ReservationLifecycleEventPayloadVersion::V1, $this->reservationId(), $transition, 2),
            ReservationLifecycleEventAggregateType::ReservationLifecycle,
            $this->reservationId(),
            'draft>confirm>requested',
            $transition->from,
            $transition->to,
            $transition->action,
            1,
            2,
        );
    }

    private function submittedEvent(): ReservationLifecycleEvent
    {
        return (new ReservationLifecycleEventCatalog)->eventFor(
            $this->reservationId(),
            new ReservationLifecycleTransition(ReservationLifecycleState::Draft, ReservationLifecycleState::Requested, ReservationLifecycleAction::Submit),
            2,
        );
    }

    private function reservationId(): ReservationId
    {
        return ReservationId::fromString('22222222-2222-4222-8222-222222222222');
    }
}

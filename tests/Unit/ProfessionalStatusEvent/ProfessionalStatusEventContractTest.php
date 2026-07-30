<?php

namespace Tests\Unit\ProfessionalStatusEvent;

use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEvent;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventCatalog;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventId;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventMetadata;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventPayload;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventPayloadVersion;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventSerializer;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventType;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;
use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ProfessionalStatusEventContractTest extends TestCase
{
    #[DataProvider('transitions')]
    public function test_catalog_mapping_payload_and_serialization_are_canonical(ProfessionalStatusTransition $transition, ProfessionalStatusEventType $type): void
    {
        $event = $this->event($transition, $type);
        $serialized = (new ProfessionalStatusEventSerializer)->serialize($event);

        self::assertSame($type, (new ProfessionalStatusEventCatalog)->typeFor($transition));
        self::assertSame($serialized, (new ProfessionalStatusEventSerializer)->serialize($event));
        self::assertSame(['eventId', 'eventType', 'payloadVersion', 'payload', 'metadata'], array_keys($event->canonical()));
        self::assertSame(['eventId', 'aggregateType', 'professionalId', 'transition', 'previousState', 'currentState', 'action', 'version', 'occurredVersion'], array_keys($event->payload->fields()));
        foreach (['name', 'registration', 'establishment', 'mandate', 'email', 'phone', 'address'] as $forbidden) {
            self::assertStringNotContainsStringIgnoringCase($forbidden, $serialized);
        }
        self::assertTrue((new ReflectionClass($event))->isReadOnly());
        self::assertTrue((new ReflectionClass($event->payload))->isReadOnly());
    }

    public function test_identity_is_deterministic_versioned_and_sha256(): void
    {
        [$transition, $type] = self::transitions()[0];
        $first = $this->event($transition, $type, 2);
        $same = $this->event($transition, $type, 2);
        $next = $this->event($transition, $type, 3);
        self::assertSame($first->payload->eventId->value, $same->payload->eventId->value);
        self::assertNotSame($first->payload->eventId->value, $next->payload->eventId->value);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first->payload->eventId->value);
    }

    public function test_uncertified_transition_is_refused(): void
    {
        $this->expectException(DomainException::class);
        (new ProfessionalStatusEventCatalog)->typeFor(new ProfessionalStatusTransition(ProfessionalStatusState::Active, ProfessionalStatusState::Active, ProfessionalStatusAction::Reactivate));
    }

    public function test_metadata_refuses_recording_before_occurrence(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ProfessionalStatusEventMetadata(ProfessionalStatusEventType::Suspended, ProfessionalStatusEventPayloadVersion::V1, $this->actor(), $this->at('2026-07-22T12:00:01+00:00'), $this->at('2026-07-22T12:00:00+00:00'));
    }

    public function test_catalog_and_versions_are_closed(): void
    {
        self::assertSame(['professional.status.suspended', 'professional.status.reactivated'], array_column(ProfessionalStatusEventType::cases(), 'value'));
        self::assertSame([1], array_column(ProfessionalStatusEventPayloadVersion::cases(), 'value'));
    }

    /** @return list<array{ProfessionalStatusTransition,ProfessionalStatusEventType}> */
    public static function transitions(): array
    {
        return [
            [new ProfessionalStatusTransition(ProfessionalStatusState::Active, ProfessionalStatusState::Suspended, ProfessionalStatusAction::Suspend), ProfessionalStatusEventType::Suspended],
            [new ProfessionalStatusTransition(ProfessionalStatusState::Suspended, ProfessionalStatusState::Active, ProfessionalStatusAction::Reactivate), ProfessionalStatusEventType::Reactivated],
        ];
    }

    private function event(ProfessionalStatusTransition $transition, ProfessionalStatusEventType $type, int $occurredVersion = 2): ProfessionalStatusEvent
    {
        $id = ProfessionalStatusId::fromString('a4500000-0000-4000-8000-000000000050');
        $version = ProfessionalStatusEventPayloadVersion::V1;
        $eventId = ProfessionalStatusEventId::derive($type, $version, $id, $transition, $occurredVersion);
        $at = $this->at('2026-07-22T12:00:00+00:00');

        return new ProfessionalStatusEvent(
            new ProfessionalStatusEventMetadata($type, $version, $this->actor(), $at, $at),
            new ProfessionalStatusEventPayload($eventId, $id, implode('>', [$transition->from->value, $transition->action->value, $transition->to->value]), $transition->from, $transition->to, $transition->action, $version->value, $occurredVersion),
        );
    }

    private function actor(): ProfessionalStatusActorId
    {
        return ProfessionalStatusActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa');
    }

    private function at(string $value): ProfessionalStatusOccurredAt
    {
        return ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable($value));
    }
}

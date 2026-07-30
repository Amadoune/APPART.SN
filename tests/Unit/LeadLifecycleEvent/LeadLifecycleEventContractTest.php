<?php

namespace Tests\Unit\LeadLifecycleEvent;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEvent;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventCatalog;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventMetadata;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventPayload;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventPayloadVersion;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventSerializer;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventType;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LeadLifecycleEventContractTest extends TestCase
{
    #[DataProvider('transitions')]
    public function test_catalog_is_exhaustive_and_serialization_is_canonical(
        LeadLifecycleTransition $transition,
        LeadLifecycleEventType $type,
    ): void {
        $event = $this->event($transition, $type);
        $serialized = (new LeadLifecycleEventSerializer)->serialize($event);

        self::assertSame($type, (new LeadLifecycleEventCatalog)->typeFor($transition));
        self::assertSame($serialized, (new LeadLifecycleEventSerializer)->serialize($event));
        self::assertSame(
            ['eventId', 'eventType', 'payloadVersion', 'payload', 'metadata'],
            array_keys($event->canonical()),
        );
        self::assertSame(
            ['eventId', 'aggregateType', 'leadId', 'transition', 'previousState', 'currentState', 'action', 'version', 'occurredVersion'],
            array_keys($event->payload->fields()),
        );
        self::assertStringNotContainsString('listingId', $serialized);
        self::assertStringNotContainsString('advertiserId', $serialized);
    }

    public function test_event_identity_is_deterministic_and_versioned(): void
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

        (new LeadLifecycleEventCatalog)->typeFor(new LeadLifecycleTransition(
            LeadLifecycleState::Closed,
            LeadLifecycleState::Closed,
            LeadLifecycleAction::Close,
        ));
    }

    public function test_envelope_refuses_an_event_type_not_owned_by_the_transition(): void
    {
        [$transition] = self::transitions()[0];

        $this->expectException(InvalidArgumentException::class);
        $this->event($transition, LeadLifecycleEventType::Rejected);
    }

    public function test_metadata_refuses_recording_before_occurrence(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LeadLifecycleEventMetadata(
            LeadLifecycleEventType::Delivered,
            LeadLifecycleEventPayloadVersion::V1,
            LeadLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
            LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:01+00:00')),
            LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00')),
        );
    }

    public function test_catalog_and_versions_are_closed(): void
    {
        self::assertCount(3, LeadLifecycleEventType::cases());
        self::assertSame([1], array_column(LeadLifecycleEventPayloadVersion::cases(), 'value'));
    }

    /**
     * @return list<array{LeadLifecycleTransition, LeadLifecycleEventType}>
     */
    public static function transitions(): array
    {
        return [
            [new LeadLifecycleTransition(LeadLifecycleState::Created, LeadLifecycleState::Delivered, LeadLifecycleAction::Deliver), LeadLifecycleEventType::Delivered],
            [new LeadLifecycleTransition(LeadLifecycleState::Created, LeadLifecycleState::Rejected, LeadLifecycleAction::Reject), LeadLifecycleEventType::Rejected],
            [new LeadLifecycleTransition(LeadLifecycleState::Delivered, LeadLifecycleState::Closed, LeadLifecycleAction::Close), LeadLifecycleEventType::Closed],
            [new LeadLifecycleTransition(LeadLifecycleState::Rejected, LeadLifecycleState::Closed, LeadLifecycleAction::Close), LeadLifecycleEventType::Closed],
        ];
    }

    private function event(
        LeadLifecycleTransition $transition,
        LeadLifecycleEventType $type,
        int $occurredVersion = 2,
    ): LeadLifecycleEvent {
        $leadId = LeadId::fromString('a4100000-0000-4000-8000-000000000098');
        $at = LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00'));
        $payloadVersion = LeadLifecycleEventPayloadVersion::V1;
        $eventId = LeadLifecycleEventId::derive($type, $payloadVersion, $leadId, $transition, $occurredVersion);

        return new LeadLifecycleEvent(
            new LeadLifecycleEventMetadata(
                $type,
                $payloadVersion,
                LeadLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
                $at,
                $at,
            ),
            new LeadLifecycleEventPayload(
                $eventId,
                $leadId,
                implode('>', [$transition->from->value, $transition->action->value, $transition->to->value]),
                $transition->from,
                $transition->to,
                $transition->action,
                $payloadVersion->value,
                $occurredVersion,
            ),
        );
    }
}

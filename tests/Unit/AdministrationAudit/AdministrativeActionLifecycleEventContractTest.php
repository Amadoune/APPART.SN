<?php

namespace Tests\Unit\AdministrationAudit;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEvent;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventCatalog;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventId;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventMetadata;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventPayload;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventPayloadVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventSerializer;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventType;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AdministrativeActionLifecycleEventContractTest extends TestCase
{
    #[DataProvider('certifiedTransitions')]
    public function test_catalog_is_bijective_and_event_is_canonical(
        AdministrativeActionLifecycleTransition $transition,
        AdministrativeActionLifecycleEventType $expectedType,
    ): void {
        $event = $this->event($transition);
        $serializer = new AdministrativeActionLifecycleEventSerializer;

        self::assertSame($expectedType, (new AdministrativeActionLifecycleEventCatalog)->typeFor($transition));
        self::assertSame($expectedType, $event->metadata->eventType);
        self::assertSame($event->payload->eventId->value, $event->canonical()['eventId']);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $event->payload->eventId->value);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $event->checksum()->value);
        self::assertSame($serializer->serialize($event), $serializer->serialize($this->event($transition)));
    }

    public function test_payload_is_minimal_and_excludes_confidential_context(): void
    {
        $json = (new AdministrativeActionLifecycleEventSerializer)->serialize(
            $this->event(new AdministrativeActionLifecycleTransition(
                AdministrativeActionLifecycleState::PendingApproval,
                AdministrativeActionLifecycleState::Approved,
                AdministrativeActionLifecycleAction::Approve,
            )),
        );

        foreach (['historicalReason', 'reasonEvidence', 'decisionContext', 'approvalId', 'decisionId', 'authorId', 'recordingDisposition', 'audit'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $json);
        }
        self::assertStringContainsString('"administrativeActionId"', $json);
        self::assertStringContainsString('"actorId"', $json);
        self::assertStringContainsString('"occurredVersion":2', $json);
    }

    public function test_uncertified_transition_is_rejected_explicitly(): void
    {
        $this->expectException(DomainException::class);
        (new AdministrativeActionLifecycleEventCatalog)->typeFor(new AdministrativeActionLifecycleTransition(
            AdministrativeActionLifecycleState::Draft,
            AdministrativeActionLifecycleState::Approved,
            AdministrativeActionLifecycleAction::Approve,
        ));
    }

    /** @return iterable<string, array{AdministrativeActionLifecycleTransition, AdministrativeActionLifecycleEventType}> */
    public static function certifiedTransitions(): iterable
    {
        yield 'direct record' => [
            new AdministrativeActionLifecycleTransition(AdministrativeActionLifecycleState::Draft, AdministrativeActionLifecycleState::Recorded, AdministrativeActionLifecycleAction::Record),
            AdministrativeActionLifecycleEventType::Recorded,
        ];
        yield 'approval request' => [
            new AdministrativeActionLifecycleTransition(AdministrativeActionLifecycleState::Draft, AdministrativeActionLifecycleState::PendingApproval, AdministrativeActionLifecycleAction::Record),
            AdministrativeActionLifecycleEventType::ApprovalRequested,
        ];
        yield 'approve' => [
            new AdministrativeActionLifecycleTransition(AdministrativeActionLifecycleState::PendingApproval, AdministrativeActionLifecycleState::Approved, AdministrativeActionLifecycleAction::Approve),
            AdministrativeActionLifecycleEventType::Approved,
        ];
        yield 'reject' => [
            new AdministrativeActionLifecycleTransition(AdministrativeActionLifecycleState::PendingApproval, AdministrativeActionLifecycleState::Rejected, AdministrativeActionLifecycleAction::Reject),
            AdministrativeActionLifecycleEventType::Rejected,
        ];
    }

    private function event(AdministrativeActionLifecycleTransition $transition): AdministrativeActionLifecycleEvent
    {
        $type = (new AdministrativeActionLifecycleEventCatalog)->typeFor($transition);
        $version = AdministrativeActionLifecycleEventPayloadVersion::V1;
        $id = AdministrativeActionId::fromString('a4700000-0000-4000-8000-000000000047');
        $eventId = AdministrativeActionLifecycleEventId::derive($type, $version, $id, $transition, 2);

        return new AdministrativeActionLifecycleEvent(
            new AdministrativeActionLifecycleEventMetadata(
                $type,
                $version,
                ActorId::fromString('decision-actor-001'),
                AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-24T12:00:00.000000+00:00')),
                AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-24T12:00:01.000000+00:00')),
            ),
            new AdministrativeActionLifecycleEventPayload(
                $eventId,
                $id,
                implode('>', [$transition->from->value, $transition->action->value, $transition->to->value]),
                $transition->from,
                $transition->to,
                $transition->action,
                1,
                2,
            ),
        );
    }
}

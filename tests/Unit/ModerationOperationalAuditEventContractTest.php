<?php

namespace Tests\Unit;

use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\FindingRecordedEventV1;
use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\ModerationOperationalAuditEventTypeV1;
use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\QueueItemClaimedEventV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationOperationalAuditEventContractTest extends TestCase
{
    #[Test]
    public function finding_event_is_immutable_deterministic_canonical_and_minimal(): void
    {
        $first = $this->finding();
        $second = $this->finding();

        self::assertSame(ModerationOperationalAuditEventTypeV1::FindingRecorded, $first->eventType());
        self::assertSame($first->eventId(), $second->eventId());
        self::assertSame($first->checksum(), $second->checksum());
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $first->checksum());
        self::assertSame(['findingId' => self::id(2)], $first->payload());
        self::assertSame([], (new \ReflectionClass($first))->getProperties(\ReflectionProperty::IS_PUBLIC));
    }

    #[Test]
    public function queue_claim_event_has_a_closed_payload_and_distinct_identity(): void
    {
        $event = new QueueItemClaimedEventV1(
            self::id(1),
            self::id(3),
            2,
            'moderation-policy-v1',
            self::now(),
            self::now(),
            self::id(4),
            self::id(5),
        );

        self::assertSame(ModerationOperationalAuditEventTypeV1::QueueItemClaimed, $event->eventType());
        self::assertSame(['queueItemId' => self::id(3)], $event->payload());
        self::assertNotSame($this->finding()->eventId(), $event->eventId());
        self::assertStringNotContainsString('actor', serialize($event));
        self::assertStringNotContainsString('evidence', serialize($event));
        self::assertStringNotContainsString('diagnostic', serialize($event));
    }

    #[Test]
    public function identity_changes_only_when_a_contract_identity_input_changes(): void
    {
        $event = $this->finding();
        $differentPayload = new FindingRecordedEventV1(
            self::id(1),
            self::id(9),
            2,
            'moderation-policy-v1',
            self::now(),
            self::now(),
            self::id(4),
            self::id(5),
        );
        $differentCausation = new FindingRecordedEventV1(
            self::id(1),
            self::id(2),
            2,
            'moderation-policy-v1',
            self::now(),
            self::now(),
            self::id(4),
            self::id(8),
        );

        self::assertSame($event->eventId(), $differentPayload->eventId());
        self::assertNotSame($event->checksum(), $differentPayload->checksum());
        self::assertNotSame($event->eventId(), $differentCausation->eventId());
    }

    private function finding(): FindingRecordedEventV1
    {
        return new FindingRecordedEventV1(
            self::id(1),
            self::id(2),
            2,
            'moderation-policy-v1',
            self::now(),
            self::now(),
            self::id(4),
            self::id(5),
        );
    }

    private static function id(int $suffix): string
    {
        return sprintf('53f30000-0000-4000-8000-%012d', $suffix);
    }

    private static function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-30T12:00:00.123456+00:00');
    }
}

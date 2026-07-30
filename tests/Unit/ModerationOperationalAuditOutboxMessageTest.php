<?php

namespace Tests\Unit;

use App\Application\ModerationOperationalAuditEventProduction\ModerationOperationalAuditOutboxMessageV1;
use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\FindingRecordedEventV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationOperationalAuditOutboxMessageTest extends TestCase
{
    #[Test]
    public function message_identity_and_transport_are_canonical_and_route_free(): void
    {
        $event = new FindingRecordedEventV1(
            $this->id(1), $this->id(2), 2, 'v1', $this->now(), $this->now(),
            $this->id(3), $this->id(4),
        );
        $first = new ModerationOperationalAuditOutboxMessageV1($event);
        $second = new ModerationOperationalAuditOutboxMessageV1($event);

        self::assertSame($first->messageId, $second->messageId);
        self::assertSame($first->fields(), $second->fields());
        self::assertSame(78, strlen($first->messageId));
        self::assertArrayNotHasKey('destination', $first->fields());
        self::assertStringNotContainsString('routing', json_encode($first->fields(), JSON_THROW_ON_ERROR));
    }

    private function id(int $suffix): string
    {
        return sprintf('53f30000-0000-4000-8000-%012d', $suffix);
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-30T12:00:00+00:00');
    }
}

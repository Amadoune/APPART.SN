<?php

namespace Tests\Unit;

use App\Application\ModerationEventRouting\ModerationOperationalAuditDestinationMatrix;
use App\Application\ModerationEventRouting\ModerationRoutingDestination;
use App\Application\ModerationOperationalAudit\ModerationOperationalAuditRecordFactory;
use App\Application\ModerationOperationalAuditEventProduction\ModerationOperationalAuditOutboxMessageV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditOperationV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\FindingRecordedEventV1;
use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\QueueItemClaimedEventV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationOperationalAuditRoutingIntegrationTest extends TestCase
{
    #[Test]
    #[DataProvider('messages')]
    public function routing_and_audit_mapping_are_closed_and_deterministic(
        ModerationOperationalAuditOutboxMessageV1 $message,
        array $expectedDestinations,
        AdministrationAuditOperationV1 $operation,
    ): void {
        $matrix = new ModerationOperationalAuditDestinationMatrix;
        self::assertSame($expectedDestinations, $matrix->destinations($message));

        $record = (new ModerationOperationalAuditRecordFactory)->map($message);
        self::assertNotNull($record);
        self::assertSame($operation, $record->operation);
        self::assertSame($message->correlationId(), $record->correlationId);
        self::assertSame($message->causationId(), $record->causationId);
        self::assertStringNotContainsString('email', serialize($record));
        self::assertStringNotContainsString('rationale', serialize($record));
    }

    /** @return iterable<string, array{ModerationOperationalAuditOutboxMessageV1, list<ModerationRoutingDestination>, AdministrationAuditOperationV1}> */
    public static function messages(): iterable
    {
        $now = new DateTimeImmutable('2026-07-30T12:00:00+00:00');
        $historical = [
            ModerationRoutingDestination::QueueProjection,
            ModerationRoutingDestination::CaseTimeline,
            ModerationRoutingDestination::DeliveryObservation,
        ];
        yield 'report submitted' => [
            new ModerationOperationalAuditOutboxMessageV1(new ModerationEventV1(
                ModerationEventTypeV1::ReportSubmitted,
                self::id(1),
                1,
                ['reportId' => self::id(2)],
                'v1',
                $now,
                $now,
                self::id(3),
                self::id(4),
            )),
            $historical,
            AdministrationAuditOperationV1::ReportSubmitted,
        ];
        yield 'report validated' => [
            new ModerationOperationalAuditOutboxMessageV1(new ModerationEventV1(
                ModerationEventTypeV1::ReportValidated,
                self::id(1),
                2,
                ['reportId' => self::id(2)],
                'v1',
                $now,
                $now,
                self::id(5),
                self::id(6),
            )),
            $historical,
            AdministrationAuditOperationV1::ReportValidated,
        ];
        yield 'finding recorded' => [
            new ModerationOperationalAuditOutboxMessageV1(new FindingRecordedEventV1(
                self::id(1), self::id(7), 3, 'v1', $now, $now, self::id(8), self::id(9),
            )),
            [ModerationRoutingDestination::DeliveryObservation],
            AdministrationAuditOperationV1::FindingRecorded,
        ];
        yield 'queue item claimed' => [
            new ModerationOperationalAuditOutboxMessageV1(new QueueItemClaimedEventV1(
                self::id(1), self::id(10), 4, 'v1', $now, $now, self::id(11), self::id(12),
            )),
            [ModerationRoutingDestination::DeliveryObservation],
            AdministrationAuditOperationV1::QueueItemClaimed,
        ];
    }

    private static function id(int $suffix): string
    {
        return sprintf('53f30000-0000-4000-8000-%012d', $suffix);
    }
}

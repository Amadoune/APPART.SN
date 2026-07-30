<?php

namespace Tests\Unit;

use App\Application\ModerationOperationalAudit\ModerationOperationalAuditRecordFactory;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditOperationV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditOutcomeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationOperationalAuditTest extends TestCase
{
    #[Test]
    public function mapper_preserves_correlation_causation_and_minimal_confidential_fields(): void
    {
        $record = (new ModerationOperationalAuditRecordFactory)->map(self::event());

        self::assertNotNull($record);
        self::assertSame(AdministrationAuditOperationV1::DecisionIssued, $record->operation);
        self::assertSame(AdministrationAuditOutcomeV1::Applied, $record->outcome);
        self::assertSame(self::id(2), $record->subjectId);
        self::assertSame(self::id(1), $record->actorId);
        self::assertSame(self::id(4), $record->correlationId);
        self::assertSame(self::id(5), $record->causationId);
        self::assertStringNotContainsString('targetAction', serialize($record));
    }

    public static function event(): ModerationEventV1
    {
        return new ModerationEventV1(
            ModerationEventTypeV1::DecisionIssued,
            self::id(1),
            1,
            ['decisionId' => self::id(2), 'targetAction' => 'suspend'],
            'moderation-policy-v1',
            self::now(),
            self::now(),
            self::id(4),
            self::id(5),
        );
    }

    public static function id(int $suffix): string
    {
        return sprintf('53f30000-0000-4000-8000-%012d', $suffix);
    }

    public static function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-30T12:00:00+00:00');
    }
}

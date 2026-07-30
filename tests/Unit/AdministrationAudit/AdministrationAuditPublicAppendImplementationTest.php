<?php

namespace Tests\Unit\AdministrationAudit;

use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditAppendResultV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditOperationV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditOutcomeV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditRecordV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditSourceOwnerV1;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\AdministrationAuditAppendConnection;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrationAuditAppendV1;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PublicAuditAppend\AdministrationAuditAppendMapperV1;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class AdministrationAuditPublicAppendImplementationTest extends TestCase
{
    #[Test]
    public function mapper_is_bijective_for_the_complete_canonical_record(): void
    {
        $record = self::record();
        $mapper = new AdministrationAuditAppendMapperV1;
        $row = $mapper->parameters($record);

        self::assertTrue($mapper->equals($record, $row));

        $row['outcome'] = AdministrationAuditOutcomeV1::Rejected->value;
        self::assertFalse($mapper->equals($record, $row));
    }

    #[Test]
    public function invalid_records_are_rejected_and_technical_failures_remain_closed(): void
    {
        $record = self::record();
        $serialized = serialize($record);
        $corrupted = unserialize(str_replace(
            $record->checksum,
            str_repeat('0', 64),
            $serialized,
        ));
        self::assertInstanceOf(AdministrationAuditRecordV1::class, $corrupted);

        $connection = $this->createStub(PDO::class);
        $append = new PostgreSqlAdministrationAuditAppendV1(
            new AdministrationAuditAppendConnection($connection),
            new AdministrationAuditAppendMapperV1,
        );
        self::assertSame(AdministrationAuditAppendResultV1::Rejected, $append->append($corrupted));

        $connection->method('inTransaction')->willThrowException(new RuntimeException('internal'));
        self::assertSame(
            AdministrationAuditAppendResultV1::DependencyUnavailable,
            $append->append($record),
        );
    }

    public static function record(
        AdministrationAuditOutcomeV1 $outcome = AdministrationAuditOutcomeV1::Applied,
    ): AdministrationAuditRecordV1 {
        return AdministrationAuditRecordV1::create(
            AdministrationAuditSourceOwnerV1::ModerationReports,
            AdministrationAuditOperationV1::DecisionIssued,
            '10000000-0000-4000-8000-000000000001',
            '10000000-0000-4000-8000-000000000002',
            $outcome,
            '10000000-0000-4000-8000-000000000003',
            '10000000-0000-4000-8000-000000000004',
            new DateTimeImmutable('2026-07-30T10:00:00.123456+00:00'),
            'moderation-policy-v1',
        );
    }
}

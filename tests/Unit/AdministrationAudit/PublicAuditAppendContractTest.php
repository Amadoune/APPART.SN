<?php

namespace Tests\Unit\AdministrationAudit;

use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditAppendResultV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditOperationV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditOutcomeV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditRecordV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditSourceOwnerV1;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PublicAuditAppendContractTest extends TestCase
{
    public function test_record_identity_and_checksum_are_canonical_and_deterministic(): void
    {
        $first = $this->record(AdministrationAuditOutcomeV1::Applied);
        $second = $this->record(AdministrationAuditOutcomeV1::Applied);
        $divergent = $this->record(AdministrationAuditOutcomeV1::Rejected);

        self::assertSame($first->recordId->value, $second->recordId->value);
        self::assertSame($first->checksum, $second->checksum);
        self::assertSame($first->recordId->value, $divergent->recordId->value);
        self::assertNotSame($first->checksum, $divergent->checksum);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $first->checksum);
        self::assertSame('70000000-0000-4000-8000-000000000001', $first->actorId);
    }

    public function test_contract_is_structurally_minimal_immutable_and_closed(): void
    {
        $reflection = new ReflectionClass(AdministrationAuditRecordV1::class);
        self::assertTrue($reflection->isReadOnly());
        self::assertSame([
            'recordId',
            'sourceOwner',
            'operation',
            'subjectId',
            'actorId',
            'outcome',
            'correlationId',
            'causationId',
            'occurredAt',
            'policyVersion',
            'checksum',
        ], array_map(
            static fn (\ReflectionProperty $property): string => $property->getName(),
            $reflection->getProperties(),
        ));
        self::assertSame([
            'applied',
            'already_applied',
            'divergent_record',
            'rejected',
            'dependency_unavailable',
        ], array_map(
            static fn (AdministrationAuditAppendResultV1 $result): string => $result->value,
            AdministrationAuditAppendResultV1::cases(),
        ));
        self::assertCount(1, AdministrationAuditSourceOwnerV1::cases());
        self::assertCount(7, AdministrationAuditOperationV1::cases());
        self::assertCount(6, AdministrationAuditOutcomeV1::cases());
    }

    public function test_invalid_identifiers_and_free_form_policy_are_rejected_fail_closed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        AdministrationAuditRecordV1::create(
            AdministrationAuditSourceOwnerV1::ModerationReports,
            AdministrationAuditOperationV1::DecisionIssued,
            'not-an-opaque-uuid',
            '70000000-0000-4000-8000-000000000001',
            AdministrationAuditOutcomeV1::Applied,
            '70000000-0000-4000-8000-000000000002',
            null,
            new DateTimeImmutable('2026-07-30T10:00:00+00:00'),
            'v1',
        );
    }

    private function record(AdministrationAuditOutcomeV1 $outcome): AdministrationAuditRecordV1
    {
        return AdministrationAuditRecordV1::create(
            AdministrationAuditSourceOwnerV1::ModerationReports,
            AdministrationAuditOperationV1::DecisionIssued,
            '70000000-0000-4000-8000-000000000003',
            '70000000-0000-4000-8000-000000000001',
            $outcome,
            '70000000-0000-4000-8000-000000000002',
            '70000000-0000-4000-8000-000000000004',
            new DateTimeImmutable('2026-07-30T10:00:00+00:00'),
            'moderation-v1',
        );
    }
}

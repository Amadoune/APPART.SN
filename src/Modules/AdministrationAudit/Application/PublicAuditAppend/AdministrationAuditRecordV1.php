<?php

namespace Appart\Modules\AdministrationAudit\Application\PublicAuditAppend;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class AdministrationAuditRecordV1
{
    private function __construct(
        public AdministrationAuditRecordIdV1 $recordId,
        public AdministrationAuditSourceOwnerV1 $sourceOwner,
        public AdministrationAuditOperationV1 $operation,
        public string $subjectId,
        public string $actorId,
        public AdministrationAuditOutcomeV1 $outcome,
        public string $correlationId,
        public ?string $causationId,
        public DateTimeImmutable $occurredAt,
        public string $policyVersion,
        public string $checksum,
    ) {}

    public static function create(
        AdministrationAuditSourceOwnerV1 $sourceOwner,
        AdministrationAuditOperationV1 $operation,
        string $subjectId,
        string $actorId,
        AdministrationAuditOutcomeV1 $outcome,
        string $correlationId,
        ?string $causationId,
        DateTimeImmutable $occurredAt,
        string $policyVersion,
    ): self {
        AdministrationAuditRecordIdV1::assertUuid($subjectId, 'subjectId');
        AdministrationAuditRecordIdV1::assertUuid($actorId, 'actorId');
        AdministrationAuditRecordIdV1::assertUuid($correlationId, 'correlationId');
        if ($causationId !== null) {
            AdministrationAuditRecordIdV1::assertUuid($causationId, 'causationId');
        }
        if (preg_match('/^[A-Za-z0-9._-]{1,64}$/', $policyVersion) !== 1) {
            throw new InvalidArgumentException('policyVersion must use the closed token format.');
        }

        $recordId = AdministrationAuditRecordIdV1::deterministic($sourceOwner, $operation, $correlationId);

        return new self(
            $recordId,
            $sourceOwner,
            $operation,
            strtolower($subjectId),
            strtolower($actorId),
            $outcome,
            strtolower($correlationId),
            $causationId === null ? null : strtolower($causationId),
            $occurredAt,
            $policyVersion,
            AdministrationAuditRecordChecksumV1::calculate(
                $recordId,
                $sourceOwner,
                $operation,
                $subjectId,
                $actorId,
                $outcome,
                $correlationId,
                $causationId,
                $occurredAt,
                $policyVersion,
            ),
        );
    }
}

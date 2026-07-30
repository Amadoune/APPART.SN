<?php

namespace Appart\Modules\AdministrationAudit\Application\PublicAuditAppend;

use DateTimeInterface;

final class AdministrationAuditRecordChecksumV1
{
    public static function calculate(
        AdministrationAuditRecordIdV1 $recordId,
        AdministrationAuditSourceOwnerV1 $sourceOwner,
        AdministrationAuditOperationV1 $operation,
        string $subjectId,
        string $actorId,
        AdministrationAuditOutcomeV1 $outcome,
        string $correlationId,
        ?string $causationId,
        DateTimeInterface $occurredAt,
        string $policyVersion,
    ): string {
        return hash('sha256', json_encode((object) [
            'actorId' => strtolower($actorId),
            'causationId' => $causationId === null ? null : strtolower($causationId),
            'contractVersion' => 1,
            'correlationId' => strtolower($correlationId),
            'occurredAt' => $occurredAt->format('Y-m-d\TH:i:s.uP'),
            'operation' => $operation->value,
            'outcome' => $outcome->value,
            'policyVersion' => $policyVersion,
            'recordId' => $recordId->value,
            'sourceOwner' => $sourceOwner->value,
            'subjectId' => strtolower($subjectId),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }
}

<?php

namespace Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PublicAuditAppend;

use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditRecordV1;

final readonly class AdministrationAuditAppendMapperV1
{
    /** @return array<string, int|string|null> */
    public function parameters(AdministrationAuditRecordV1 $record): array
    {
        return [
            'record_id' => $record->recordId->value,
            'source_owner' => $record->sourceOwner->value,
            'operation' => $record->operation->value,
            'subject_id' => $record->subjectId,
            'actor_id' => $record->actorId,
            'outcome' => $record->outcome->value,
            'correlation_id' => $record->correlationId,
            'causation_id' => $record->causationId,
            'occurred_at' => $record->occurredAt->format('Y-m-d\TH:i:s.uP'),
            'policy_version' => $record->policyVersion,
            'contract_version' => 1,
            'record_checksum' => $record->checksum,
        ];
    }

    /** @param array<string, mixed> $row */
    public function equals(AdministrationAuditRecordV1 $record, array $row): bool
    {
        $expected = $this->parameters($record);
        $utc = new \DateTimeZone('UTC');
        $expected['occurred_at'] = $record->occurredAt
            ->setTimezone($utc)
            ->format('Y-m-d\TH:i:s.uP');
        foreach ($expected as $key => $value) {
            $stored = $row[$key] ?? null;
            if ($key === 'occurred_at' && is_string($stored)) {
                $stored = (new \DateTimeImmutable($stored))
                    ->setTimezone($utc)
                    ->format('Y-m-d\TH:i:s.uP');
            }
            if ($key === 'contract_version') {
                $stored = is_numeric($stored) ? (int) $stored : $stored;
            }
            if ($stored !== $value) {
                return false;
            }
        }

        return true;
    }
}

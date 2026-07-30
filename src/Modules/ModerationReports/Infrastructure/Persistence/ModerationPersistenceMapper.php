<?php

namespace Appart\Modules\ModerationReports\Infrastructure\Persistence;

use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationCasePersistenceState;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceRecord;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationQueueItemState;
use DateTimeImmutable;
use JsonException;

final class ModerationPersistenceMapper
{
    /** @return array<string, int|string|null> */
    public function caseParameters(ModerationCasePersistenceState $state): array
    {
        return [
            'case_id' => $state->caseId,
            'target_type' => $state->targetType,
            'target_id' => $state->targetId,
            'status' => $state->status,
            'current_decision_id' => $state->currentDecisionId,
            'version' => $state->version,
            'intent_id' => $state->intentId,
            'intent_checksum' => $state->intentChecksum,
            'updated_at' => $state->updatedAt->format('Y-m-d H:i:s.uP'),
        ];
    }

    /** @return array<string, int|string> */
    public function recordParameters(
        string $caseId,
        string $identityColumn,
        ModerationPersistenceRecord $record,
        int $caseVersion,
    ): array {
        $payload = $this->encode($record->payload);

        return [
            'case_id' => $caseId,
            $identityColumn => $record->id,
            'case_version' => $caseVersion,
            'payload' => $payload,
            'payload_checksum' => hash('sha256', $payload),
            'recorded_at' => $record->recordedAt->format('Y-m-d H:i:s.uP'),
        ];
    }

    /**
     * @param  array<string, mixed>  $root
     * @param  list<array<string, mixed>>  $reports
     * @param  list<array<string, mixed>>  $findings
     * @param  list<array<string, mixed>>  $decisions
     */
    public function state(array $root, array $reports, array $findings, array $decisions): ModerationCasePersistenceState
    {
        return new ModerationCasePersistenceState(
            (string) $root['case_id'],
            (string) $root['target_type'],
            (string) $root['target_id'],
            (string) $root['status'],
            $root['current_decision_id'] === null ? null : (string) $root['current_decision_id'],
            (int) $root['version'],
            (string) $root['last_intent_id'],
            (string) $root['last_intent_checksum'],
            new DateTimeImmutable((string) $root['updated_at']),
            $this->records($reports, 'report_id'),
            $this->records($findings, 'finding_id'),
            $this->records($decisions, 'decision_id'),
        );
    }

    /** @param array<string, mixed> $row */
    public function decision(array $row): ModerationPersistenceRecord
    {
        return $this->record($row, 'decision_id');
    }

    /** @return array<string, int|string|null> */
    public function queueParameters(ModerationQueueItemState $item): array
    {
        return [
            'queue_item_id' => $item->queueItemId,
            'case_id' => $item->caseId,
            'priority' => $item->priority,
            'category' => $item->category,
            'state' => $item->state,
            'lease_id' => $item->leaseId,
            'claim_owner_id' => $item->claimOwnerId,
            'lease_expires_at' => $item->leaseExpiresAt?->format('Y-m-d H:i:s.uP'),
            'source_version' => $item->sourceVersion,
            'updated_at' => $item->updatedAt->format('Y-m-d H:i:s.uP'),
        ];
    }

    /** @param array<string, mixed> $row */
    public function queue(array $row): ModerationQueueItemState
    {
        return new ModerationQueueItemState(
            (string) $row['queue_item_id'],
            (string) $row['case_id'],
            (int) $row['priority'],
            (string) $row['category'],
            (string) $row['state'],
            $row['lease_id'] === null ? null : (string) $row['lease_id'],
            $row['claim_owner_id'] === null ? null : (string) $row['claim_owner_id'],
            $row['lease_expires_at'] === null ? null : new DateTimeImmutable((string) $row['lease_expires_at']),
            (int) $row['source_version'],
            new DateTimeImmutable((string) $row['updated_at']),
        );
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<ModerationPersistenceRecord>
     */
    private function records(array $rows, string $identityColumn): array
    {
        return array_map(fn (array $row): ModerationPersistenceRecord => $this->record($row, $identityColumn), $rows);
    }

    /** @param array<string, mixed> $row */
    private function record(array $row, string $identityColumn): ModerationPersistenceRecord
    {
        $payload = json_decode((string) $row['payload'], true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($payload) || array_is_list($payload)) {
            throw new JsonException('A moderation persistence payload must be a JSON object.');
        }

        return new ModerationPersistenceRecord(
            (string) $row[$identityColumn],
            $payload,
            new DateTimeImmutable((string) $row['recorded_at']),
        );
    }

    /** @param array<string, mixed> $payload */
    private function encode(array $payload): string
    {
        return json_encode(
            (object) $this->canonicalize($payload),
            JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_SLASHES,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function canonicalize(array $payload): array
    {
        ksort($payload, SORT_STRING);
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = array_is_list($value)
                    ? array_map(fn (mixed $item): mixed => is_array($item) ? $this->canonicalize($item) : $item, $value)
                    : $this->canonicalize($value);
            }
        }

        return $payload;
    }
}

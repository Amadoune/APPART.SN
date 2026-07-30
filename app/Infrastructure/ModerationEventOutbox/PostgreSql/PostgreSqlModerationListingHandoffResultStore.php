<?php

namespace App\Infrastructure\ModerationEventOutbox\PostgreSql;

use App\Application\ModerationListingHandoff\Contract\ModerationListingHandoffResultStore;
use App\Application\ModerationListingHandoff\ModerationListingHandoffRecord;
use App\Application\ModerationListingHandoff\ModerationListingHandoffStatus;
use DateTimeImmutable;
use PDO;
use Throwable;

final readonly class PostgreSqlModerationListingHandoffResultStore implements ModerationListingHandoffResultStore
{
    public function __construct(private PDO $connection) {}

    public function append(ModerationListingHandoffRecord $record): bool
    {
        try {
            $statement = $this->connection->prepare(
                'INSERT INTO moderation_reports.listing_handoff_results(
                    message_id,case_id,decision_id,command_id,command_checksum,status,recorded_at
                 ) VALUES(
                    :message_id,CAST(:case_id AS uuid),CAST(:decision_id AS uuid),
                    CAST(:command_id AS uuid),:checksum,:status,CAST(:recorded_at AS timestamptz)
                 ) ON CONFLICT(message_id,status) DO NOTHING',
            );
            $statement->execute($this->parameters($record));
            if ($statement->rowCount() === 1) {
                return true;
            }
            $existing = $this->byStatus($record->messageId, $record->status);

            return $existing !== null
                && $existing->caseId === $record->caseId
                && $existing->decisionId === $record->decisionId
                && $existing->commandId === $record->commandId
                && $existing->checksum === $record->checksum;
        } catch (Throwable) {
            return false;
        }
    }

    public function latest(string $messageId): ?ModerationListingHandoffRecord
    {
        try {
            $statement = $this->connection->prepare(
                'SELECT message_id,case_id::text,decision_id::text,command_id::text,
                        command_checksum,status,recorded_at
                 FROM moderation_reports.listing_handoff_results
                 WHERE message_id=:message_id ORDER BY revision DESC LIMIT 1',
            );
            $statement->execute(['message_id' => $messageId]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);

            return is_array($row) ? $this->record($row) : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function byStatus(
        string $messageId,
        ModerationListingHandoffStatus $status,
    ): ?ModerationListingHandoffRecord {
        $statement = $this->connection->prepare(
            'SELECT message_id,case_id::text,decision_id::text,command_id::text,
                    command_checksum,status,recorded_at
             FROM moderation_reports.listing_handoff_results
             WHERE message_id=:message_id AND status=:status',
        );
        $statement->execute(['message_id' => $messageId, 'status' => $status->value]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->record($row) : null;
    }

    /** @return array<string, string|null> */
    private function parameters(ModerationListingHandoffRecord $record): array
    {
        return [
            'message_id' => $record->messageId,
            'case_id' => $record->caseId,
            'decision_id' => $record->decisionId,
            'command_id' => $record->commandId === '' ? null : $record->commandId,
            'checksum' => $record->checksum === '' ? null : $record->checksum,
            'status' => $record->status->value,
            'recorded_at' => $record->recordedAt->format('Y-m-d H:i:s.uP'),
        ];
    }

    /** @param array<string, mixed> $row */
    private function record(array $row): ModerationListingHandoffRecord
    {
        return new ModerationListingHandoffRecord(
            (string) $row['message_id'],
            (string) $row['case_id'],
            (string) $row['decision_id'],
            (string) ($row['command_id'] ?? ''),
            (string) ($row['command_checksum'] ?? ''),
            ModerationListingHandoffStatus::from((string) $row['status']),
            new DateTimeImmutable((string) $row['recorded_at']),
        );
    }
}

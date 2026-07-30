<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationCommandResultV1;
use Appart\Modules\ListingLifecycle\Application\ModerationIntent\Contract\ListingModerationIntentStore;
use Appart\Modules\ListingLifecycle\Application\ModerationIntent\ListingModerationIntentReservationV1;
use Appart\Modules\ListingLifecycle\Application\ModerationIntent\ListingModerationIntentStateV1;
use DateTimeImmutable;
use PDO;
use RuntimeException;

final readonly class PostgreSqlListingModerationIntentStore implements ListingModerationIntentStore
{
    public function __construct(private PDO $connection) {}

    public function reserve(
        string $commandId,
        string $checksum,
        DateTimeImmutable $recordedAt,
    ): ListingModerationIntentReservationV1 {
        $this->guard($commandId, $checksum);
        $lock = $this->connection->prepare(
            'SELECT pg_advisory_xact_lock(hashtextextended(:command_id, 0))',
        );
        $lock->execute(['command_id' => $commandId]);
        $insert = $this->connection->prepare(
            'INSERT INTO listing_lifecycle.moderation_command_intents(
                command_id,checksum,recorded_at
             ) VALUES(CAST(:command_id AS uuid),:checksum,CAST(:recorded_at AS timestamptz))
             ON CONFLICT(command_id) DO NOTHING',
        );
        $insert->execute([
            'command_id' => $commandId,
            'checksum' => $checksum,
            'recorded_at' => $recordedAt->format('Y-m-d H:i:s.uP'),
        ]);
        if ($insert->rowCount() === 1) {
            return ListingModerationIntentReservationV1::Reserved;
        }
        $existing = $this->find($commandId);
        if ($existing === null || ! hash_equals($existing->checksum, $checksum)) {
            return ListingModerationIntentReservationV1::DivergentIntent;
        }

        return ListingModerationIntentReservationV1::AlreadyApplied;
    }

    public function find(string $commandId): ?ListingModerationIntentStateV1
    {
        $statement = $this->connection->prepare(
            'SELECT i.command_id::text,i.checksum,r.result
             FROM listing_lifecycle.moderation_command_intents i
             LEFT JOIN listing_lifecycle.moderation_command_intent_results r USING(command_id)
             WHERE i.command_id=CAST(:command_id AS uuid)',
        );
        $statement->execute(['command_id' => $commandId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (! is_array($row)) {
            return null;
        }

        return new ListingModerationIntentStateV1(
            (string) $row['command_id'],
            (string) $row['checksum'],
            $row['result'] === null
                ? null
                : ListingModerationCommandResultV1::from((string) $row['result']),
        );
    }

    public function complete(
        string $commandId,
        string $checksum,
        ListingModerationCommandResultV1 $result,
        DateTimeImmutable $recordedAt,
    ): bool {
        $this->guard($commandId, $checksum);
        $intent = $this->find($commandId);
        if ($intent === null || ! hash_equals($intent->checksum, $checksum)) {
            return false;
        }
        $statement = $this->connection->prepare(
            'INSERT INTO listing_lifecycle.moderation_command_intent_results(
                command_id,result,recorded_at
             ) VALUES(CAST(:command_id AS uuid),:result,CAST(:recorded_at AS timestamptz))
             ON CONFLICT(command_id) DO NOTHING',
        );
        $statement->execute([
            'command_id' => $commandId,
            'result' => $result->value,
            'recorded_at' => $recordedAt->format('Y-m-d H:i:s.uP'),
        ]);
        if ($statement->rowCount() === 1) {
            return true;
        }
        $stored = $this->find($commandId);

        return $stored?->terminalResult === $result;
    }

    private function guard(string $commandId, string $checksum): void
    {
        if (
            preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $commandId) !== 1
            || preg_match('/^[0-9a-f]{64}$/', $checksum) !== 1
        ) {
            throw new RuntimeException('Invalid Listing moderation intent identity.');
        }
    }
}

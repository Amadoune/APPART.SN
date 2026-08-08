<?php

namespace Appart\Modules\LegacyMigration\Infrastructure\Outbox;

use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationCutoverDeliveryPayload;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationCutoverDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationCutoverDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationInventoryDeliveryPayload;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationInventoryDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationInventoryDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationQuarantineDeliveryPayload;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationQuarantineDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationQuarantineDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationReconciliationDeliveryPayload;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationReconciliationDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationReconciliationDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationWaveDeliveryPayload;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationWaveDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationWaveDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationCutoverEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationInventoryEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationQuarantineEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationReconciliationEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationWaveEventType;
use Appart\Modules\LegacyMigration\Application\Outbox\LegacyMigrationOutboxPolicy;
use Appart\Modules\LegacyMigration\Application\Outbox\LegacyMigrationOutboxReader;
use Appart\Modules\LegacyMigration\Application\Outbox\LegacyMigrationOutboxResult;
use Appart\Modules\LegacyMigration\Application\Outbox\LegacyMigrationOutboxStatus;
use Appart\Modules\LegacyMigration\Application\Outbox\LegacyMigrationOutboxWriter;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final readonly class PostgreSqlLegacyMigrationOutboxRepository implements LegacyMigrationOutboxReader, LegacyMigrationOutboxWriter
{
    private const SAVEPOINT = 'legacy_migration_outbox';

    public function __construct(private PDO $connection, private LegacyMigrationOutboxPolicy $policy) {}

    public function append(LegacyMigrationInventoryDeliveryV1|LegacyMigrationWaveDeliveryV1|LegacyMigrationReconciliationDeliveryV1|LegacyMigrationQuarantineDeliveryV1|LegacyMigrationCutoverDeliveryV1 $delivery): LegacyMigrationOutboxResult
    {
        $entry = $this->policy->prepare($delivery);
        try {
            return $this->transactional(function () use ($entry): LegacyMigrationOutboxResult {
                $payload = $this->policy->canonical($entry->delivery);
                $statement = $this->connection->prepare('INSERT INTO legacy_migration.outbox_messages(message_id,event_type,delivery_status,observed_at,payload,message_checksum) VALUES(:message_id,:event_type,:delivery_status,CAST(:observed_at AS timestamptz),CAST(:payload AS jsonb),:message_checksum) ON CONFLICT(message_id) DO NOTHING');
                $statement->execute([
                    'message_id' => $entry->messageId,
                    'event_type' => $entry->delivery->type->value,
                    'delivery_status' => $entry->delivery->payload->status->value,
                    'observed_at' => $entry->delivery->payload->observedAt,
                    'payload' => $payload,
                    'message_checksum' => $this->policy->checksum($entry->delivery),
                ]);
                if ($statement->rowCount() === 1) {
                    return $entry;
                }

                $existing = $this->connection->prepare('SELECT payload::text,message_checksum,retry_count FROM legacy_migration.outbox_messages WHERE message_id=:message_id FOR UPDATE');
                $existing->execute(['message_id' => $entry->messageId]);
                $row = $existing->fetch(PDO::FETCH_ASSOC);
                if (! is_array($row)) {
                    return new LegacyMigrationOutboxResult($entry->messageId, LegacyMigrationOutboxStatus::DivergentMessage, $entry->delivery, 0);
                }
                $same = json_decode((string) $row['payload'], true, 512, JSON_THROW_ON_ERROR) === json_decode($payload, true, 512, JSON_THROW_ON_ERROR)
                    && hash_equals((string) $row['message_checksum'], $this->policy->checksum($entry->delivery));

                return new LegacyMigrationOutboxResult(
                    $entry->messageId,
                    $same ? LegacyMigrationOutboxStatus::AlreadyApplied : LegacyMigrationOutboxStatus::DivergentMessage,
                    $entry->delivery,
                    (int) $row['retry_count'],
                );
            });
        } catch (PDOException) {
            return new LegacyMigrationOutboxResult($entry->messageId, LegacyMigrationOutboxStatus::DependencyUnavailable, $delivery, 0);
        }
    }

    public function pending(int $limit): array
    {
        if ($limit < 1 || $limit > 100) {
            throw new RuntimeException('Legacy Migration outbox limit must be between 1 and 100.');
        }
        $statement = $this->connection->prepare('SELECT message_id,event_type,delivery_status,observed_at,retry_count FROM legacy_migration.outbox_messages WHERE delivered_at IS NULL AND retry_count<:max_retries ORDER BY created_at,message_id LIMIT :limit');
        $statement->bindValue('max_retries', LegacyMigrationOutboxPolicy::MAX_RETRIES, PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return array_map(fn (array $row): LegacyMigrationOutboxResult => new LegacyMigrationOutboxResult(
            (string) $row['message_id'],
            LegacyMigrationOutboxStatus::Applied,
            $this->delivery((string) $row['event_type'], (string) $row['delivery_status'], $this->canonicalObservedAt((string) $row['observed_at'])),
            (int) $row['retry_count'],
        ), $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    private function delivery(string $type, string $status, string $observedAt): LegacyMigrationInventoryDeliveryV1|LegacyMigrationWaveDeliveryV1|LegacyMigrationReconciliationDeliveryV1|LegacyMigrationQuarantineDeliveryV1|LegacyMigrationCutoverDeliveryV1
    {
        return match ($type) {
            LegacyMigrationInventoryEventType::Observed->value => new LegacyMigrationInventoryDeliveryV1(LegacyMigrationInventoryEventType::Observed, new LegacyMigrationInventoryDeliveryPayload(LegacyMigrationInventoryDeliveryStatus::from($status), $observedAt)),
            LegacyMigrationWaveEventType::Observed->value => new LegacyMigrationWaveDeliveryV1(LegacyMigrationWaveEventType::Observed, new LegacyMigrationWaveDeliveryPayload(LegacyMigrationWaveDeliveryStatus::from($status), $observedAt)),
            LegacyMigrationReconciliationEventType::Observed->value => new LegacyMigrationReconciliationDeliveryV1(LegacyMigrationReconciliationEventType::Observed, new LegacyMigrationReconciliationDeliveryPayload(LegacyMigrationReconciliationDeliveryStatus::from($status), $observedAt)),
            LegacyMigrationQuarantineEventType::Observed->value => new LegacyMigrationQuarantineDeliveryV1(LegacyMigrationQuarantineEventType::Observed, new LegacyMigrationQuarantineDeliveryPayload(LegacyMigrationQuarantineDeliveryStatus::from($status), $observedAt)),
            LegacyMigrationCutoverEventType::Observed->value => new LegacyMigrationCutoverDeliveryV1(LegacyMigrationCutoverEventType::Observed, new LegacyMigrationCutoverDeliveryPayload(LegacyMigrationCutoverDeliveryStatus::from($status), $observedAt)),
            default => throw new RuntimeException('Legacy Migration outbox event type is corrupted.'),
        };
    }

    private function canonicalObservedAt(string $observedAt): string
    {
        return (new DateTimeImmutable($observedAt))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }

    private function transactional(callable $operation): LegacyMigrationOutboxResult
    {
        $owner = ! $this->connection->inTransaction();
        $owner ? $this->connection->beginTransaction() : $this->connection->exec('SAVEPOINT '.self::SAVEPOINT);
        try {
            $result = $operation();
            $owner ? $this->connection->commit() : $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);

            return $result;
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            } else {
                $this->connection->exec('ROLLBACK TO SAVEPOINT '.self::SAVEPOINT);
                $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);
            }
            throw $error;
        }
    }
}

<?php

namespace Appart\Modules\AdministrationConsole\Infrastructure\Outbox;

use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationAuditDeliveryPayload;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationAuditDeliveryStatus;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationAuditDeliveryV1;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationOperatorDeliveryPayload;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationOperatorDeliveryStatus;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationOperatorDeliveryV1;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationQueueDeliveryPayload;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationQueueDeliveryStatus;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationQueueDeliveryV1;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationAuditEventType;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationOperatorEventType;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationQueueEventType;
use Appart\Modules\AdministrationConsole\Application\Outbox\AdministrationConsoleOutboxPolicy;
use Appart\Modules\AdministrationConsole\Application\Outbox\AdministrationConsoleOutboxReader;
use Appart\Modules\AdministrationConsole\Application\Outbox\AdministrationConsoleOutboxResult;
use Appart\Modules\AdministrationConsole\Application\Outbox\AdministrationConsoleOutboxStatus;
use Appart\Modules\AdministrationConsole\Application\Outbox\AdministrationConsoleOutboxWriter;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final readonly class PostgreSqlAdministrationConsoleOutboxRepository implements AdministrationConsoleOutboxReader, AdministrationConsoleOutboxWriter
{
    private const SAVEPOINT = 'administration_console_outbox';

    public function __construct(private PDO $connection, private AdministrationConsoleOutboxPolicy $policy) {}

    public function append(AdministrationOperatorDeliveryV1|AdministrationQueueDeliveryV1|AdministrationAuditDeliveryV1 $delivery): AdministrationConsoleOutboxResult
    {
        $entry = $this->policy->prepare($delivery);
        try {
            return $this->transactional(function () use ($entry): AdministrationConsoleOutboxResult {
                $payload = $this->policy->canonical($entry->delivery);
                $statement = $this->connection->prepare('INSERT INTO administration_console.outbox_messages(message_id,event_type,delivery_status,observed_at,payload,message_checksum) VALUES(:message_id,:event_type,:delivery_status,CAST(:observed_at AS timestamptz),CAST(:payload AS jsonb),:message_checksum) ON CONFLICT(message_id) DO NOTHING');
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

                $existing = $this->connection->prepare('SELECT payload::text,message_checksum,retry_count FROM administration_console.outbox_messages WHERE message_id=:message_id FOR UPDATE');
                $existing->execute(['message_id' => $entry->messageId]);
                $row = $existing->fetch(PDO::FETCH_ASSOC);
                if (! is_array($row)) {
                    return new AdministrationConsoleOutboxResult($entry->messageId, AdministrationConsoleOutboxStatus::DivergentMessage, $entry->delivery, 0);
                }
                $same = json_decode((string) $row['payload'], true, 512, JSON_THROW_ON_ERROR) === json_decode($payload, true, 512, JSON_THROW_ON_ERROR)
                    && hash_equals((string) $row['message_checksum'], $this->policy->checksum($entry->delivery));

                return new AdministrationConsoleOutboxResult(
                    $entry->messageId,
                    $same ? AdministrationConsoleOutboxStatus::AlreadyApplied : AdministrationConsoleOutboxStatus::DivergentMessage,
                    $entry->delivery,
                    (int) $row['retry_count'],
                );
            });
        } catch (PDOException) {
            return new AdministrationConsoleOutboxResult($entry->messageId, AdministrationConsoleOutboxStatus::DependencyUnavailable, $delivery, 0);
        }
    }

    public function pending(int $limit): array
    {
        if ($limit < 1 || $limit > 100) {
            throw new RuntimeException('Administration Console outbox limit must be between 1 and 100.');
        }
        $statement = $this->connection->prepare('SELECT message_id,event_type,delivery_status,observed_at,retry_count FROM administration_console.outbox_messages WHERE delivered_at IS NULL AND retry_count<:max_retries ORDER BY created_at,message_id LIMIT :limit');
        $statement->bindValue('max_retries', AdministrationConsoleOutboxPolicy::MAX_RETRIES, PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return array_map(fn (array $row): AdministrationConsoleOutboxResult => new AdministrationConsoleOutboxResult(
            (string) $row['message_id'],
            AdministrationConsoleOutboxStatus::Applied,
            $this->delivery((string) $row['event_type'], (string) $row['delivery_status'], $this->canonicalObservedAt((string) $row['observed_at'])),
            (int) $row['retry_count'],
        ), $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    private function delivery(string $type, string $status, string $observedAt): AdministrationOperatorDeliveryV1|AdministrationQueueDeliveryV1|AdministrationAuditDeliveryV1
    {
        return match ($type) {
            AdministrationOperatorEventType::Observed->value => new AdministrationOperatorDeliveryV1(AdministrationOperatorEventType::Observed, new AdministrationOperatorDeliveryPayload(AdministrationOperatorDeliveryStatus::from($status), $observedAt)),
            AdministrationQueueEventType::Observed->value => new AdministrationQueueDeliveryV1(AdministrationQueueEventType::Observed, new AdministrationQueueDeliveryPayload(AdministrationQueueDeliveryStatus::from($status), $observedAt)),
            AdministrationAuditEventType::Observed->value => new AdministrationAuditDeliveryV1(AdministrationAuditEventType::Observed, new AdministrationAuditDeliveryPayload(AdministrationAuditDeliveryStatus::from($status), $observedAt)),
            default => throw new RuntimeException('Administration Console outbox event type is corrupted.'),
        };
    }

    private function canonicalObservedAt(string $observedAt): string
    {
        return (new DateTimeImmutable($observedAt))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }

    private function transactional(callable $operation): AdministrationConsoleOutboxResult
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

<?php

namespace Appart\Modules\Notifications\Infrastructure\Outbox;

use Appart\Modules\Notifications\Application\Delivery\NotificationDeliveryPayload;
use Appart\Modules\Notifications\Application\Delivery\NotificationDeliveryStatus;
use Appart\Modules\Notifications\Application\Delivery\NotificationDeliveryV1;
use Appart\Modules\Notifications\Application\Event\NotificationEventType;
use Appart\Modules\Notifications\Application\Outbox\NotificationOutboxPolicy;
use Appart\Modules\Notifications\Application\Outbox\NotificationOutboxReader;
use Appart\Modules\Notifications\Application\Outbox\NotificationOutboxResult;
use Appart\Modules\Notifications\Application\Outbox\NotificationOutboxStatus;
use Appart\Modules\Notifications\Application\Outbox\NotificationOutboxWriter;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final readonly class PostgreSqlNotificationsOutboxRepository implements NotificationOutboxReader, NotificationOutboxWriter
{
    private const SAVEPOINT = 'notifications_outbox';

    public function __construct(private PDO $connection, private NotificationOutboxPolicy $policy) {}

    public function append(NotificationDeliveryV1 $delivery): NotificationOutboxResult
    {
        $entry = $this->policy->prepare($delivery);
        try {
            return $this->transactional(function () use ($entry): NotificationOutboxResult {
                $payload = $this->policy->canonical($entry->delivery);
                $statement = $this->connection->prepare('INSERT INTO notifications.notification_outbox(message_id,event_type,delivery_status,observed_at,payload,message_checksum) VALUES(:message_id,:event_type,:delivery_status,CAST(:observed_at AS timestamptz),CAST(:payload AS jsonb),:message_checksum) ON CONFLICT(message_id) DO NOTHING');
                $statement->execute(['message_id' => $entry->messageId, 'event_type' => $entry->delivery->payload->type->value, 'delivery_status' => $entry->delivery->payload->status->value, 'observed_at' => $entry->delivery->payload->observedAt, 'payload' => $payload, 'message_checksum' => $this->policy->checksum($entry->delivery)]);
                if ($statement->rowCount() === 1) {
                    return $entry;
                }

                $existing = $this->connection->prepare('SELECT payload::text,message_checksum,retry_count FROM notifications.notification_outbox WHERE message_id=:message_id FOR UPDATE');
                $existing->execute(['message_id' => $entry->messageId]);
                $row = $existing->fetch(PDO::FETCH_ASSOC);
                if (! is_array($row)) {
                    return new NotificationOutboxResult($entry->messageId, NotificationOutboxStatus::DivergentMessage, $entry->delivery, 0);
                }
                $same = json_decode((string) $row['payload'], true, 512, JSON_THROW_ON_ERROR) === json_decode($payload, true, 512, JSON_THROW_ON_ERROR) && hash_equals((string) $row['message_checksum'], $this->policy->checksum($entry->delivery));

                return new NotificationOutboxResult($entry->messageId, $same ? NotificationOutboxStatus::AlreadyApplied : NotificationOutboxStatus::DivergentMessage, $entry->delivery, (int) $row['retry_count']);
            });
        } catch (PDOException) {
            return new NotificationOutboxResult($entry->messageId, NotificationOutboxStatus::DependencyUnavailable, $delivery, 0);
        }
    }

    public function pending(int $limit): array
    {
        if ($limit < 1 || $limit > 100) {
            throw new RuntimeException('Notifications outbox limit must be between 1 and 100.');
        }
        $statement = $this->connection->prepare('SELECT message_id,event_type,delivery_status,observed_at,retry_count FROM notifications.notification_outbox WHERE delivered_at IS NULL AND retry_count<:max_retries ORDER BY created_at,message_id LIMIT :limit');
        $statement->bindValue('max_retries', NotificationOutboxPolicy::MAX_RETRIES, PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return array_map(static fn (array $row): NotificationOutboxResult => new NotificationOutboxResult((string) $row['message_id'], NotificationOutboxStatus::Applied, new NotificationDeliveryV1(new NotificationDeliveryPayload(NotificationEventType::from((string) $row['event_type']), NotificationDeliveryStatus::from((string) $row['delivery_status']), (new DateTimeImmutable((string) $row['observed_at']))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\\TH:i:s.u\\Z'))), (int) $row['retry_count']), $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    private function transactional(callable $operation): NotificationOutboxResult
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
            }throw $error;
        }
    }
}

<?php

namespace Appart\Modules\ContentSeo\Infrastructure\Outbox;

use Appart\Modules\ContentSeo\Application\Delivery\EditorialContentDeliveryPayload;
use Appart\Modules\ContentSeo\Application\Delivery\EditorialContentDeliveryStatus;
use Appart\Modules\ContentSeo\Application\Delivery\EditorialContentDeliveryV1;
use Appart\Modules\ContentSeo\Application\Delivery\OperationalSeoDeliveryPayload;
use Appart\Modules\ContentSeo\Application\Delivery\OperationalSeoDeliveryStatus;
use Appart\Modules\ContentSeo\Application\Delivery\OperationalSeoDeliveryV1;
use Appart\Modules\ContentSeo\Application\Event\EditorialContentEventType;
use Appart\Modules\ContentSeo\Application\Event\OperationalSeoEventType;
use Appart\Modules\ContentSeo\Application\Outbox\ContentSeoOutboxPolicy;
use Appart\Modules\ContentSeo\Application\Outbox\ContentSeoOutboxReader;
use Appart\Modules\ContentSeo\Application\Outbox\ContentSeoOutboxResult;
use Appart\Modules\ContentSeo\Application\Outbox\ContentSeoOutboxStatus;
use Appart\Modules\ContentSeo\Application\Outbox\ContentSeoOutboxWriter;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final readonly class PostgreSqlContentSeoOutboxRepository implements ContentSeoOutboxReader, ContentSeoOutboxWriter
{
    private const SAVEPOINT = 'content_seo_outbox';

    public function __construct(private PDO $connection, private ContentSeoOutboxPolicy $policy) {}

    public function append(EditorialContentDeliveryV1|OperationalSeoDeliveryV1 $delivery): ContentSeoOutboxResult
    {
        $entry = $this->policy->prepare($delivery);
        try {
            return $this->transactional(function () use ($entry): ContentSeoOutboxResult {
                $payload = $this->policy->canonical($entry->delivery);
                $kind = $entry->delivery instanceof EditorialContentDeliveryV1 ? 'editorial' : 'operational_seo';
                $statement = $this->connection->prepare('INSERT INTO content_seo.content_seo_outbox(message_id,delivery_kind,event_type,delivery_status,observed_at,payload,message_checksum) VALUES(:message_id,:delivery_kind,:event_type,:delivery_status,CAST(:observed_at AS timestamptz),CAST(:payload AS jsonb),:message_checksum) ON CONFLICT(message_id) DO NOTHING');
                $statement->execute(['message_id' => $entry->messageId, 'delivery_kind' => $kind, 'event_type' => $entry->delivery->payload->type->value, 'delivery_status' => $entry->delivery->payload->status->value, 'observed_at' => $entry->delivery->payload->observedAt, 'payload' => $payload, 'message_checksum' => $this->policy->checksum($entry->delivery)]);
                if ($statement->rowCount() === 1) {
                    return $entry;
                }
                $existing = $this->connection->prepare('SELECT payload::text,message_checksum,retry_count FROM content_seo.content_seo_outbox WHERE message_id=:message_id FOR UPDATE');
                $existing->execute(['message_id' => $entry->messageId]);
                $row = $existing->fetch(PDO::FETCH_ASSOC);
                if (! is_array($row)) {
                    return new ContentSeoOutboxResult($entry->messageId, ContentSeoOutboxStatus::DivergentMessage, $entry->delivery, 0);
                }
                $same = json_decode((string) $row['payload'], true, 512, JSON_THROW_ON_ERROR) === json_decode($payload, true, 512, JSON_THROW_ON_ERROR) && hash_equals((string) $row['message_checksum'], $this->policy->checksum($entry->delivery));

                return new ContentSeoOutboxResult($entry->messageId, $same ? ContentSeoOutboxStatus::AlreadyApplied : ContentSeoOutboxStatus::DivergentMessage, $entry->delivery, (int) $row['retry_count']);
            });
        } catch (PDOException) {
            return new ContentSeoOutboxResult($entry->messageId, ContentSeoOutboxStatus::DependencyUnavailable, $delivery, 0);
        }
    }

    public function pending(int $limit): array
    {
        if ($limit < 1 || $limit > 100) {
            throw new RuntimeException('ContentSeo outbox limit must be between 1 and 100.');
        }
        $statement = $this->connection->prepare('SELECT message_id,delivery_kind,event_type,delivery_status,observed_at,retry_count FROM content_seo.content_seo_outbox WHERE delivered_at IS NULL AND retry_count<:max_retries ORDER BY created_at,message_id LIMIT :limit');
        $statement->bindValue('max_retries', ContentSeoOutboxPolicy::MAX_RETRIES, PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return array_map(static function (array $row): ContentSeoOutboxResult {
            $observedAt = (new DateTimeImmutable((string) $row['observed_at']))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\\TH:i:s.u\\Z');
            $delivery = $row['delivery_kind'] === 'editorial'
                ? new EditorialContentDeliveryV1(new EditorialContentDeliveryPayload(EditorialContentEventType::from((string) $row['event_type']), EditorialContentDeliveryStatus::from((string) $row['delivery_status']), $observedAt))
                : new OperationalSeoDeliveryV1(new OperationalSeoDeliveryPayload(OperationalSeoEventType::from((string) $row['event_type']), OperationalSeoDeliveryStatus::from((string) $row['delivery_status']), $observedAt));

            return new ContentSeoOutboxResult((string) $row['message_id'], ContentSeoOutboxStatus::Applied, $delivery, (int) $row['retry_count']);
        }, $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    private function transactional(callable $operation): ContentSeoOutboxResult
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

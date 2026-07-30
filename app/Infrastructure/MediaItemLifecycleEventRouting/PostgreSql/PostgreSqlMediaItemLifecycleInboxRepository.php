<?php

namespace App\Infrastructure\MediaItemLifecycleEventRouting\PostgreSql;

use App\Application\MediaItemLifecycleEventRouting\MediaItemLifecycleInboxStore;
use App\Application\MediaItemLifecycleEventRouting\MediaItemLifecycleInboxStoreResult;
use App\Application\MediaItemLifecycleEventRouting\MediaItemLifecycleInboxStoreStatus;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleTransportEnvelope;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleTransportSerializer;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlMediaItemLifecycleInboxRepository implements MediaItemLifecycleInboxStore
{
    public function __construct(private PDO $connection, private MediaItemLifecycleTransportSerializer $serializer) {}

    public function store(MediaItemLifecycleTransportEnvelope $envelope): MediaItemLifecycleInboxStoreResult
    {
        $canonicalEvent = $envelope->payload->fields()['canonicalEvent'];
        $transportEnvelope = $this->serializer->serialize($envelope);
        $inboxId = 'miei:'.hash('sha256', $envelope->messageId);
        $ownsTransaction = false;

        try {
            if (! $this->connection->inTransaction()) {
                $this->connection->beginTransaction();
                $ownsTransaction = true;
            }

            $lock = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:message_id, 0))');
            $lock->execute(['message_id' => $envelope->messageId]);
            $statement = $this->connection->prepare("INSERT INTO media.media_item_lifecycle_event_inbox (inbox_id,message_id,message_type,transport_version,canonical_event,source,business_event_id,payload_checksum,transport_envelope,status,delivery_attempts) VALUES (:inbox_id,:message_id,:message_type,:transport_version,:canonical_event,:source,:business_event_id,:payload_checksum,:transport_envelope,'pending',0) ON CONFLICT (message_id) DO NOTHING");
            $statement->execute([
                'inbox_id' => $inboxId,
                'message_id' => $envelope->messageId,
                'message_type' => $envelope->messageType,
                'transport_version' => $envelope->transportVersion,
                'canonical_event' => $canonicalEvent,
                'source' => $envelope->metadata->source,
                'business_event_id' => $envelope->metadata->businessEventId,
                'payload_checksum' => $envelope->metadata->payloadChecksum,
                'transport_envelope' => $transportEnvelope,
            ]);
            $result = $statement->rowCount() === 1
                ? new MediaItemLifecycleInboxStoreResult(MediaItemLifecycleInboxStoreStatus::Stored)
                : $this->resultForExisting($envelope, $inboxId, $canonicalEvent, $transportEnvelope);

            if ($ownsTransaction) {
                $this->connection->commit();
            }

            return $result;
        } catch (PDOException $error) {
            $this->rollBackOwnedTransaction($ownsTransaction);
            if (str_starts_with($error->getCode(), '08') || in_array($error->getCode(), ['57P01', '57P02', '57P03'], true)) {
                return new MediaItemLifecycleInboxStoreResult(MediaItemLifecycleInboxStoreStatus::Unavailable);
            }
            if (str_starts_with($error->getCode(), '22') || str_starts_with($error->getCode(), '23')) {
                return new MediaItemLifecycleInboxStoreResult(MediaItemLifecycleInboxStoreStatus::Rejected);
            }

            return new MediaItemLifecycleInboxStoreResult(MediaItemLifecycleInboxStoreStatus::RetryableFailure);
        } catch (Throwable) {
            $this->rollBackOwnedTransaction($ownsTransaction);

            return new MediaItemLifecycleInboxStoreResult(MediaItemLifecycleInboxStoreStatus::RetryableFailure);
        }
    }

    private function resultForExisting(MediaItemLifecycleTransportEnvelope $envelope, string $inboxId, string $canonicalEvent, string $transportEnvelope): MediaItemLifecycleInboxStoreResult
    {
        $statement = $this->connection->prepare('SELECT inbox_id,message_type,transport_version,canonical_event,source,business_event_id,payload_checksum,transport_envelope,status,delivery_attempts FROM media.media_item_lifecycle_event_inbox WHERE message_id=:message_id');
        $statement->execute(['message_id' => $envelope->messageId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $identical = is_array($row)
            && $row['inbox_id'] === $inboxId
            && $row['message_type'] === $envelope->messageType
            && (int) $row['transport_version'] === $envelope->transportVersion
            && $row['canonical_event'] === $canonicalEvent
            && $row['source'] === $envelope->metadata->source
            && $row['business_event_id'] === $envelope->metadata->businessEventId
            && $row['payload_checksum'] === $envelope->metadata->payloadChecksum
            && $row['transport_envelope'] === $transportEnvelope
            && $row['status'] === 'pending'
            && (int) $row['delivery_attempts'] === 0;

        return new MediaItemLifecycleInboxStoreResult($identical ? MediaItemLifecycleInboxStoreStatus::AlreadyStored : MediaItemLifecycleInboxStoreStatus::Rejected);
    }

    private function rollBackOwnedTransaction(bool $ownsTransaction): void
    {
        if ($ownsTransaction && $this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }
}

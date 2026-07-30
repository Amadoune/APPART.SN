<?php

namespace App\Infrastructure\PlaceLifecycleEventRouting\PostgreSql;

use App\Application\PlaceLifecycleEventRouting\PlaceLifecycleInboxStore;
use App\Application\PlaceLifecycleEventRouting\PlaceLifecycleInboxStoreResult;
use App\Application\PlaceLifecycleEventRouting\PlaceLifecycleInboxStoreStatus;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleTransportEnvelope;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleTransportSerializer;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlPlaceLifecycleInboxRepository implements PlaceLifecycleInboxStore
{
    public function __construct(
        private PDO $connection,
        private PlaceLifecycleTransportSerializer $serializer,
    ) {}

    public function store(PlaceLifecycleTransportEnvelope $envelope): PlaceLifecycleInboxStoreResult
    {
        $messageId = $envelope->messageId->value;
        $canonicalEvent = $envelope->payload->fields()['canonicalEvent'];
        $transportEnvelope = $this->serializer->serialize($envelope);
        $inboxId = 'pli:'.hash('sha256', $messageId);
        $ownsTransaction = false;

        try {
            if (! $this->connection->inTransaction()) {
                $this->connection->beginTransaction();
                $ownsTransaction = true;
            }

            $lock = $this->connection->prepare(
                'SELECT pg_advisory_xact_lock(hashtextextended(:message_id, 0))',
            );
            $lock->execute(['message_id' => $messageId]);
            $statement = $this->connection->prepare(
                "INSERT INTO geography.place_lifecycle_event_inbox
                (inbox_id,message_id,message_type,transport_version,canonical_event,source,event_id,payload_checksum,transport_envelope,status,delivery_attempts)
                VALUES
                (:inbox_id,:message_id,:message_type,:transport_version,:canonical_event,:source,:event_id,:payload_checksum,:transport_envelope,'pending',0)
                ON CONFLICT (message_id) DO NOTHING",
            );
            $statement->execute([
                'inbox_id' => $inboxId,
                'message_id' => $messageId,
                'message_type' => $envelope->messageType,
                'transport_version' => $envelope->transportVersion->value,
                'canonical_event' => $canonicalEvent,
                'source' => $envelope->metadata->source,
                'event_id' => $envelope->metadata->eventId,
                'payload_checksum' => $envelope->metadata->payloadChecksum->value,
                'transport_envelope' => $transportEnvelope,
            ]);
            $result = $statement->rowCount() === 1
                ? new PlaceLifecycleInboxStoreResult(PlaceLifecycleInboxStoreStatus::Stored)
                : $this->resultForExisting($envelope, $inboxId, $canonicalEvent, $transportEnvelope);

            if ($ownsTransaction) {
                $this->connection->commit();
            }

            return $result;
        } catch (PDOException $error) {
            $this->rollBackOwnedTransaction($ownsTransaction);

            if (str_starts_with($error->getCode(), '08')
                || in_array($error->getCode(), ['57P01', '57P02', '57P03'], true)) {
                return new PlaceLifecycleInboxStoreResult(PlaceLifecycleInboxStoreStatus::Unavailable);
            }

            if (str_starts_with($error->getCode(), '22') || str_starts_with($error->getCode(), '23')) {
                return new PlaceLifecycleInboxStoreResult(PlaceLifecycleInboxStoreStatus::Rejected);
            }

            return new PlaceLifecycleInboxStoreResult(PlaceLifecycleInboxStoreStatus::RetryableFailure);
        } catch (Throwable) {
            $this->rollBackOwnedTransaction($ownsTransaction);

            return new PlaceLifecycleInboxStoreResult(PlaceLifecycleInboxStoreStatus::RetryableFailure);
        }
    }

    private function resultForExisting(
        PlaceLifecycleTransportEnvelope $envelope,
        string $inboxId,
        string $canonicalEvent,
        string $transportEnvelope,
    ): PlaceLifecycleInboxStoreResult {
        $statement = $this->connection->prepare(
            'SELECT inbox_id,message_type,transport_version,canonical_event,source,event_id,payload_checksum,transport_envelope,status,delivery_attempts
            FROM geography.place_lifecycle_event_inbox
            WHERE message_id=:message_id',
        );
        $statement->execute(['message_id' => $envelope->messageId->value]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $identical = is_array($row)
            && $row['inbox_id'] === $inboxId
            && $row['message_type'] === $envelope->messageType
            && (int) $row['transport_version'] === $envelope->transportVersion->value
            && $row['canonical_event'] === $canonicalEvent
            && $row['source'] === $envelope->metadata->source
            && $row['event_id'] === $envelope->metadata->eventId
            && $row['payload_checksum'] === $envelope->metadata->payloadChecksum->value
            && $row['transport_envelope'] === $transportEnvelope
            && $row['status'] === 'pending'
            && (int) $row['delivery_attempts'] === 0;

        return new PlaceLifecycleInboxStoreResult(
            $identical
                ? PlaceLifecycleInboxStoreStatus::AlreadyStored
                : PlaceLifecycleInboxStoreStatus::Rejected,
        );
    }

    private function rollBackOwnedTransaction(bool $ownsTransaction): void
    {
        if ($ownsTransaction && $this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }
}

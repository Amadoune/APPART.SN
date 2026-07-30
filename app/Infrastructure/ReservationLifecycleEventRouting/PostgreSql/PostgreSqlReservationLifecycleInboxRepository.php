<?php

namespace App\Infrastructure\ReservationLifecycleEventRouting\PostgreSql;

use App\Application\ReservationLifecycleEventRouting\ReservationLifecycleInboxStore;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleRoutingResult;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleRoutingStatus;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleTransportEnvelope;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleTransportSerializer;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlReservationLifecycleInboxRepository implements ReservationLifecycleInboxStore
{
    public function __construct(
        private PDO $connection,
        private ReservationLifecycleTransportSerializer $serializer,
    ) {}

    public function store(ReservationLifecycleTransportEnvelope $envelope): ReservationLifecycleRoutingResult
    {
        $canonicalEvent = $envelope->payload->fields()['canonicalEvent'];
        $transportEnvelope = $this->serializer->serialize($envelope);
        $inboxId = 'rlei:'.hash('sha256', $envelope->messageId);
        $ownsTransaction = false;

        try {
            if (! $this->connection->inTransaction()) {
                $this->connection->beginTransaction();
                $ownsTransaction = true;
            }
            $lock = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:message_id, 0))');
            $lock->execute(['message_id' => $envelope->messageId]);

            $statement = $this->connection->prepare('INSERT INTO reservation_lifecycle.reservation_lifecycle_event_inbox (inbox_id,message_id,message_type,transport_version,canonical_event,source,business_event_id,payload_checksum,transport_envelope) VALUES (:inbox_id,:message_id,:message_type,:transport_version,:canonical_event,:source,:business_event_id,:payload_checksum,:transport_envelope) ON CONFLICT (message_id) DO NOTHING');
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
                ? new ReservationLifecycleRoutingResult(ReservationLifecycleRoutingStatus::Stored)
                : $this->resultForExisting($envelope, $inboxId, $canonicalEvent, $transportEnvelope);

            if ($ownsTransaction) {
                $this->connection->commit();
            }

            return $result;
        } catch (PDOException $error) {
            $this->rollBackOwnedTransaction($ownsTransaction);
            if (str_starts_with($error->getCode(), '22') || str_starts_with($error->getCode(), '23')) {
                return new ReservationLifecycleRoutingResult(ReservationLifecycleRoutingStatus::CorruptedEnvelope);
            }

            return new ReservationLifecycleRoutingResult(ReservationLifecycleRoutingStatus::PersistenceCorrupted);
        } catch (Throwable) {
            $this->rollBackOwnedTransaction($ownsTransaction);

            return new ReservationLifecycleRoutingResult(ReservationLifecycleRoutingStatus::PersistenceCorrupted);
        }
    }

    private function resultForExisting(
        ReservationLifecycleTransportEnvelope $envelope,
        string $inboxId,
        string $canonicalEvent,
        string $transportEnvelope,
    ): ReservationLifecycleRoutingResult {
        $existing = $this->connection->prepare('SELECT inbox_id,message_type,transport_version,canonical_event,source,business_event_id,payload_checksum,transport_envelope FROM reservation_lifecycle.reservation_lifecycle_event_inbox WHERE message_id=:message_id');
        $existing->execute(['message_id' => $envelope->messageId]);
        $row = $existing->fetch(PDO::FETCH_ASSOC);
        $identical = is_array($row)
            && $row['inbox_id'] === $inboxId
            && $row['message_type'] === $envelope->messageType
            && (int) $row['transport_version'] === $envelope->transportVersion
            && $row['canonical_event'] === $canonicalEvent
            && $row['source'] === $envelope->metadata->source
            && $row['business_event_id'] === $envelope->metadata->businessEventId
            && $row['payload_checksum'] === $envelope->metadata->payloadChecksum
            && $row['transport_envelope'] === $transportEnvelope;

        return new ReservationLifecycleRoutingResult(
            $identical ? ReservationLifecycleRoutingStatus::AlreadyStored : ReservationLifecycleRoutingStatus::CorruptedEnvelope,
        );
    }

    private function rollBackOwnedTransaction(bool $ownsTransaction): void
    {
        if ($ownsTransaction && $this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }
}

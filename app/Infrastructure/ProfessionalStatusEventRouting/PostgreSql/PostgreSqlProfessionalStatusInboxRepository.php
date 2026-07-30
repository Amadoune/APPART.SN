<?php

namespace App\Infrastructure\ProfessionalStatusEventRouting\PostgreSql;

use App\Application\ProfessionalStatusEventRouting\ProfessionalStatusInboxStore;
use App\Application\ProfessionalStatusEventRouting\ProfessionalStatusInboxStoreResult;
use App\Application\ProfessionalStatusEventRouting\ProfessionalStatusInboxStoreStatus;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusTransportEnvelope;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusTransportSerializer;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlProfessionalStatusInboxRepository implements ProfessionalStatusInboxStore
{
    public function __construct(private PDO $connection, private ProfessionalStatusTransportSerializer $serializer) {}

    public function store(ProfessionalStatusTransportEnvelope $envelope): ProfessionalStatusInboxStoreResult
    {
        $canonicalEvent = $envelope->payload->fields()['canonicalEvent'];
        $transportEnvelope = $this->serializer->serialize($envelope);
        $inboxId = 'psei:'.hash('sha256', $envelope->messageId);
        $ownsTransaction = false;

        try {
            if (! $this->connection->inTransaction()) {
                $this->connection->beginTransaction();
                $ownsTransaction = true;
            }

            $lock = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:message_id, 0))');
            $lock->execute(['message_id' => $envelope->messageId]);
            $statement = $this->connection->prepare("INSERT INTO professionals.professional_status_event_inbox (inbox_id,message_id,message_type,transport_version,canonical_event,source,business_event_id,payload_checksum,transport_envelope,status,delivery_attempts) VALUES (:inbox_id,:message_id,:message_type,:transport_version,:canonical_event,:source,:business_event_id,:payload_checksum,:transport_envelope,'pending',0) ON CONFLICT (message_id) DO NOTHING");
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
                ? new ProfessionalStatusInboxStoreResult(ProfessionalStatusInboxStoreStatus::Stored)
                : $this->resultForExisting($envelope, $inboxId, $canonicalEvent, $transportEnvelope);

            if ($ownsTransaction) {
                $this->connection->commit();
            }

            return $result;
        } catch (PDOException $error) {
            $this->rollBackOwnedTransaction($ownsTransaction);
            if (str_starts_with($error->getCode(), '08') || in_array($error->getCode(), ['57P01', '57P02', '57P03'], true)) {
                return new ProfessionalStatusInboxStoreResult(ProfessionalStatusInboxStoreStatus::Unavailable);
            }
            if (str_starts_with($error->getCode(), '22') || str_starts_with($error->getCode(), '23')) {
                return new ProfessionalStatusInboxStoreResult(ProfessionalStatusInboxStoreStatus::Rejected);
            }

            return new ProfessionalStatusInboxStoreResult(ProfessionalStatusInboxStoreStatus::RetryableFailure);
        } catch (Throwable) {
            $this->rollBackOwnedTransaction($ownsTransaction);

            return new ProfessionalStatusInboxStoreResult(ProfessionalStatusInboxStoreStatus::RetryableFailure);
        }
    }

    private function resultForExisting(ProfessionalStatusTransportEnvelope $envelope, string $inboxId, string $canonicalEvent, string $transportEnvelope): ProfessionalStatusInboxStoreResult
    {
        $statement = $this->connection->prepare('SELECT inbox_id,message_type,transport_version,canonical_event,source,business_event_id,payload_checksum,transport_envelope,status,delivery_attempts FROM professionals.professional_status_event_inbox WHERE message_id=:message_id');
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

        return new ProfessionalStatusInboxStoreResult($identical ? ProfessionalStatusInboxStoreStatus::AlreadyStored : ProfessionalStatusInboxStoreStatus::Rejected);
    }

    private function rollBackOwnedTransaction(bool $ownsTransaction): void
    {
        if ($ownsTransaction && $this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }
}

<?php

namespace App\Infrastructure\MediaIngestionEventOutbox\PostgreSql;

use App\Application\MediaIngestionEventOutbox\Contract\MediaIngestionOutboxReader;
use App\Application\MediaIngestionEventOutbox\Contract\MediaIngestionOutboxWriter;
use App\Application\MediaIngestionEventOutbox\MediaIngestionOutboxDelivery;
use App\Application\MediaIngestionEventOutbox\MediaIngestionOutboxWriteResult;
use App\Application\MediaIngestionEventRouting\MediaIngestionRoutingDestination;
use App\Application\MediaIngestionEventTransport\MediaIngestionDeliveryMessageV1;
use App\Application\MediaIngestionEventTransport\MediaIngestionEventTransportSerializer;
use DateTimeImmutable;
use InvalidArgumentException;
use PDO;
use Throwable;

final readonly class PostgreSqlMediaIngestionOutbox implements MediaIngestionOutboxReader, MediaIngestionOutboxWriter
{
    public function __construct(
        private PDO $connection,
        private MediaIngestionEventTransportSerializer $serializer,
    ) {}

    public function append(MediaIngestionDeliveryMessageV1 $message, array $destinations): MediaIngestionOutboxWriteResult
    {
        if ($destinations === []) {
            return MediaIngestionOutboxWriteResult::DivergentMessage;
        }
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }
        try {
            $serialized = $this->serializer->serialize($message);
            $event = $message->event;
            $statement = $this->connection->prepare(
                'INSERT INTO media_ingestion.event_outbox_messages
                 (message_id,event_id,event_type,asset_id,aggregate_version,canonical_transport,payload_checksum,occurred_at,recorded_at)
                 VALUES(:message_id,:event_id,:event_type,CAST(:asset_id AS uuid),:aggregate_version,:transport,:checksum,CAST(:occurred_at AS timestamptz),CAST(:recorded_at AS timestamptz))
                 ON CONFLICT DO NOTHING',
            );
            $statement->execute([
                'message_id' => $message->messageId, 'event_id' => $event->eventId,
                'event_type' => $event->type->value, 'asset_id' => $event->assetId,
                'aggregate_version' => $event->aggregateVersion, 'transport' => $serialized,
                'checksum' => $message->payloadChecksum,
                'occurred_at' => $event->occurredAt->format('Y-m-d\TH:i:s.uP'),
                'recorded_at' => $event->recordedAt->format('Y-m-d\TH:i:s.uP'),
            ]);
            $created = $statement->rowCount() === 1;
            if (! $created && ! $this->matches($message, $serialized)) {
                if ($owner) {
                    $this->connection->rollBack();
                }

                return MediaIngestionOutboxWriteResult::DivergentMessage;
            }
            $delivery = $this->connection->prepare(
                "INSERT INTO media_ingestion.event_outbox_deliveries
                 (message_id,destination,status,attempts) VALUES(:message_id,:destination,'pending',0)
                 ON CONFLICT (message_id,destination) DO NOTHING",
            );
            $deliveryCreated = false;
            foreach (array_values(array_unique($destinations, SORT_REGULAR)) as $destination) {
                $delivery->execute(['message_id' => $message->messageId, 'destination' => $destination->value]);
                $deliveryCreated = $deliveryCreated || $delivery->rowCount() === 1;
            }
            if ($owner) {
                $this->connection->commit();
            }

            return $created || $deliveryCreated
                ? MediaIngestionOutboxWriteResult::Applied
                : MediaIngestionOutboxWriteResult::AlreadyApplied;
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            }
            throw $error;
        }
    }

    public function claimNext(string $owner, DateTimeImmutable $now): ?MediaIngestionOutboxDelivery
    {
        if (preg_match('/^[a-zA-Z0-9._-]{1,96}$/', $owner) !== 1) {
            throw new InvalidArgumentException('Invalid Media Ingestion Outbox claim owner.');
        }
        $this->connection->beginTransaction();
        try {
            $statement = $this->connection->prepare(
                "SELECT d.message_id,d.destination,d.attempts,m.canonical_transport
                 FROM media_ingestion.event_outbox_deliveries d
                 JOIN media_ingestion.event_outbox_messages m USING(message_id)
                 WHERE (d.status='pending'
                    OR (d.status='retry_scheduled' AND d.available_at<=CAST(:now AS timestamptz))
                    OR (d.status='claimed' AND d.claimed_until<=CAST(:now AS timestamptz)))
                 ORDER BY d.available_at,d.message_id,d.destination
                 FOR UPDATE OF d SKIP LOCKED LIMIT 1",
            );
            $statement->execute(['now' => $now->format('Y-m-d\TH:i:s.uP')]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            if (! is_array($row)) {
                $this->connection->commit();

                return null;
            }
            $claim = $this->connection->prepare(
                "UPDATE media_ingestion.event_outbox_deliveries
                 SET status='claimed',attempts=attempts+1,claim_owner=:owner,
                     claimed_until=CAST(:until AS timestamptz)
                 WHERE message_id=:message_id AND destination=:destination",
            );
            $claim->execute([
                'owner' => $owner, 'until' => $now->modify('+30 seconds')->format('Y-m-d\TH:i:s.uP'),
                'message_id' => $row['message_id'], 'destination' => $row['destination'],
            ]);
            $this->connection->commit();

            return new MediaIngestionOutboxDelivery(
                $this->serializer->restore((string) $row['canonical_transport']),
                MediaIngestionRoutingDestination::from((string) $row['destination']),
                ((int) $row['attempts']) + 1,
                $owner,
            );
        } catch (Throwable $error) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $error;
        }
    }

    public function markDelivered(MediaIngestionOutboxDelivery $delivery, DateTimeImmutable $at): bool
    {
        return $this->claimedUpdate($delivery, "status='delivered',claim_owner=NULL,claimed_until=NULL,delivered_at=CAST(:value AS timestamptz),last_error_code=NULL", $at->format('Y-m-d\TH:i:s.uP'));
    }

    public function scheduleRetry(MediaIngestionOutboxDelivery $delivery, DateTimeImmutable $availableAt, string $errorCode): bool
    {
        return $this->claimedUpdate($delivery, "status='retry_scheduled',claim_owner=NULL,claimed_until=NULL,available_at=CAST(:value AS timestamptz),last_error_code=:error", $availableAt->format('Y-m-d\TH:i:s.uP'), $errorCode);
    }

    public function quarantine(MediaIngestionOutboxDelivery $delivery, string $errorCode): bool
    {
        return $this->claimedUpdate($delivery, "status='quarantined',claim_owner=NULL,claimed_until=NULL,last_error_code=:error", '', $errorCode);
    }

    private function matches(MediaIngestionDeliveryMessageV1 $message, string $serialized): bool
    {
        $statement = $this->connection->prepare(
            'SELECT message_id,canonical_transport,payload_checksum FROM media_ingestion.event_outbox_messages
             WHERE message_id=:message_id OR event_id=:event_id',
        );
        $statement->execute(['message_id' => $message->messageId, 'event_id' => $message->event->eventId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) && $row['message_id'] === $message->messageId
            && $row['canonical_transport'] === $serialized
            && $row['payload_checksum'] === $message->payloadChecksum;
    }

    private function claimedUpdate(
        MediaIngestionOutboxDelivery $delivery,
        string $set,
        string $value,
        ?string $error = null,
    ): bool {
        $statement = $this->connection->prepare(
            "UPDATE media_ingestion.event_outbox_deliveries SET {$set}
             WHERE message_id=:message_id AND destination=:destination
               AND status='claimed' AND claim_owner=:owner",
        );
        $parameters = [
            'message_id' => $delivery->message->messageId,
            'destination' => $delivery->destination->value,
            'owner' => $delivery->claimOwner,
        ];
        if (str_contains($set, ':value')) {
            $parameters['value'] = $value;
        }
        if (str_contains($set, ':error')) {
            $parameters['error'] = $error;
        }
        $statement->execute($parameters);

        return $statement->rowCount() === 1;
    }
}

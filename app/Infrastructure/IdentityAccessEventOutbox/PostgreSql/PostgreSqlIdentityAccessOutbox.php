<?php

namespace App\Infrastructure\IdentityAccessEventOutbox\PostgreSql;

use App\Application\IdentityAccessEventOutbox\Contract\IdentityAccessOutboxReader;
use App\Application\IdentityAccessEventOutbox\Contract\IdentityAccessOutboxWriter;
use App\Application\IdentityAccessEventOutbox\IdentityAccessOutboxDelivery;
use App\Application\IdentityAccessEventOutbox\IdentityAccessOutboxWriteResult;
use App\Application\IdentityAccessEventRouting\IdentityAccessRoutingDestination;
use App\Application\IdentityAccessEventTransport\IdentityAccessDeliveryMessageV1;
use App\Application\IdentityAccessEventTransport\IdentityAccessEventTransportSerializer;
use DateTimeImmutable;
use PDO;
use Throwable;

final readonly class PostgreSqlIdentityAccessOutbox implements IdentityAccessOutboxReader, IdentityAccessOutboxWriter
{
    public function __construct(
        private PDO $connection,
        private IdentityAccessEventTransportSerializer $serializer,
    ) {}

    public function append(
        IdentityAccessDeliveryMessageV1 $message,
        array $destinations,
    ): IdentityAccessOutboxWriteResult {
        if ($destinations === []) {
            return IdentityAccessOutboxWriteResult::DivergentMessage;
        }

        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }
        try {
            $serialized = $this->serializer->serialize($message);
            $statement = $this->connection->prepare(
                'INSERT INTO identity_access_completion.event_outbox_messages
                 (message_id,event_id,event_type,account_id,aggregate_version,canonical_transport,payload_checksum,occurred_at,recorded_at)
                 VALUES(:message_id,:event_id,:event_type,CAST(:account_id AS uuid),:aggregate_version,:transport,:checksum,CAST(:occurred_at AS timestamptz),CAST(:recorded_at AS timestamptz))
                 ON CONFLICT DO NOTHING',
            );
            $event = $message->event;
            $statement->execute([
                'message_id' => $message->messageId,
                'event_id' => $event->eventId,
                'event_type' => $event->type->value,
                'account_id' => $event->accountId->value,
                'aggregate_version' => $event->aggregateVersion,
                'transport' => $serialized,
                'checksum' => $message->payloadChecksum,
                'occurred_at' => $event->occurredAt->format('Y-m-d\TH:i:s.uP'),
                'recorded_at' => $event->recordedAt->format('Y-m-d\TH:i:s.uP'),
            ]);

            $created = $statement->rowCount() === 1;
            if (! $created && ! $this->matches($message, $serialized)) {
                if ($owner) {
                    $this->connection->rollBack();
                }

                return IdentityAccessOutboxWriteResult::DivergentMessage;
            }

            $delivery = $this->connection->prepare(
                "INSERT INTO identity_access_completion.event_outbox_deliveries
                 (message_id,destination,status,attempts)
                 VALUES(:message_id,:destination,'pending',0)
                 ON CONFLICT (message_id,destination) DO NOTHING",
            );
            $deliveryCreated = false;
            foreach (array_values(array_unique($destinations, SORT_REGULAR)) as $destination) {
                $delivery->execute([
                    'message_id' => $message->messageId,
                    'destination' => $destination->value,
                ]);
                $deliveryCreated = $deliveryCreated || $delivery->rowCount() === 1;
            }
            if ($owner) {
                $this->connection->commit();
            }

            return $created || $deliveryCreated
                ? IdentityAccessOutboxWriteResult::Applied
                : IdentityAccessOutboxWriteResult::AlreadyApplied;
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            }
            throw $error;
        }
    }

    public function claimNext(string $owner, DateTimeImmutable $now): ?IdentityAccessOutboxDelivery
    {
        if (preg_match('/^[a-zA-Z0-9._-]{1,96}$/', $owner) !== 1) {
            throw new \InvalidArgumentException('Invalid IAM Outbox claim owner.');
        }

        $this->connection->beginTransaction();
        try {
            $statement = $this->connection->prepare(
                "SELECT d.message_id,d.destination,d.attempts,m.canonical_transport
                 FROM identity_access_completion.event_outbox_deliveries d
                 JOIN identity_access_completion.event_outbox_messages m USING(message_id)
                 WHERE (d.status='pending' OR (d.status='retry_scheduled' AND d.available_at<=CAST(:now AS timestamptz))
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
                "UPDATE identity_access_completion.event_outbox_deliveries
                 SET status='claimed',attempts=attempts+1,claim_owner=:owner,
                     claimed_until=CAST(:until AS timestamptz)
                 WHERE message_id=:message_id AND destination=:destination",
            );
            $claim->execute([
                'owner' => $owner,
                'until' => $now->modify('+30 seconds')->format('Y-m-d\TH:i:s.uP'),
                'message_id' => $row['message_id'],
                'destination' => $row['destination'],
            ]);
            $this->connection->commit();

            return new IdentityAccessOutboxDelivery(
                $this->serializer->restore((string) $row['canonical_transport']),
                IdentityAccessRoutingDestination::from((string) $row['destination']),
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

    public function markDelivered(IdentityAccessOutboxDelivery $delivery, DateTimeImmutable $at): bool
    {
        return $this->claimedUpdate(
            $delivery,
            "status='delivered',claim_owner=NULL,claimed_until=NULL,delivered_at=CAST(:value AS timestamptz),last_error_code=NULL",
            $at->format('Y-m-d\TH:i:s.uP'),
        );
    }

    public function scheduleRetry(
        IdentityAccessOutboxDelivery $delivery,
        DateTimeImmutable $availableAt,
        string $errorCode,
    ): bool {
        return $this->claimedUpdate(
            $delivery,
            "status='retry_scheduled',claim_owner=NULL,claimed_until=NULL,available_at=CAST(:value AS timestamptz),last_error_code=:error",
            $availableAt->format('Y-m-d\TH:i:s.uP'),
            $errorCode,
        );
    }

    public function quarantine(IdentityAccessOutboxDelivery $delivery, string $errorCode): bool
    {
        return $this->claimedUpdate(
            $delivery,
            "status='quarantined',claim_owner=NULL,claimed_until=NULL,last_error_code=:error",
            '',
            $errorCode,
        );
    }

    private function matches(IdentityAccessDeliveryMessageV1 $message, string $serialized): bool
    {
        $statement = $this->connection->prepare(
            'SELECT event_id,canonical_transport,payload_checksum
             FROM identity_access_completion.event_outbox_messages WHERE message_id=:message_id',
        );
        $statement->execute(['message_id' => $message->messageId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row)
            && $row['event_id'] === $message->event->eventId
            && $row['canonical_transport'] === $serialized
            && $row['payload_checksum'] === $message->payloadChecksum;
    }

    private function claimedUpdate(
        IdentityAccessOutboxDelivery $delivery,
        string $set,
        string $value,
        ?string $error = null,
    ): bool {
        $statement = $this->connection->prepare(
            "UPDATE identity_access_completion.event_outbox_deliveries SET {$set}
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

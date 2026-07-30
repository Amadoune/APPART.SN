<?php

namespace App\Infrastructure\ModerationEventOutbox\PostgreSql;

use App\Application\ModerationAtomicOperation\Contract\ModerationOutboxAppenderV1;
use App\Application\ModerationAtomicOperation\ModerationOutboxAppendResult;
use App\Application\ModerationEventOutbox\Contract\ModerationOutboxReaderV1;
use App\Application\ModerationEventOutbox\ModerationOutboxDelivery;
use App\Application\ModerationEventRouting\DeterministicModerationEventRouter;
use App\Application\ModerationEventRouting\ModerationRoutingDestination;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Application\ModerationEventTransport\ModerationEventTransportSerializer;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use DateTimeImmutable;
use InvalidArgumentException;
use PDO;
use Throwable;

final readonly class PostgreSqlModerationOutbox implements ModerationOutboxAppenderV1, ModerationOutboxReaderV1
{
    public function __construct(
        private PDO $connection,
        private ModerationEventTransportSerializer $serializer,
        private DeterministicModerationEventRouter $router,
    ) {}

    public function append(ModerationDeliveryMessageV1 $message): ModerationOutboxAppendResult
    {
        return $this->transaction(function () use ($message): ModerationOutboxAppendResult {
            $lock = $this->connection->prepare(
                'SELECT pg_advisory_xact_lock(hashtextextended(:message_id, 0))',
            );
            $lock->execute(['message_id' => $message->messageId]);
            $transport = $this->serializer->serialize($message);
            $event = $message->event;
            $insert = $this->connection->prepare(
                'INSERT INTO moderation_reports.outbox_messages(
                    message_id,event_id,event_type,case_id,aggregate_version,
                    canonical_transport,payload_checksum,correlation_id,causation_id,
                    occurred_at,recorded_at
                 ) VALUES (
                    :message_id,CAST(:event_id AS uuid),:event_type,CAST(:case_id AS uuid),
                    :aggregate_version,CAST(:transport AS jsonb),:checksum,
                    CAST(:correlation_id AS uuid),CAST(:causation_id AS uuid),
                    CAST(:occurred_at AS timestamptz),CAST(:recorded_at AS timestamptz)
                 ) ON CONFLICT DO NOTHING',
            );
            $insert->execute([
                'message_id' => $message->messageId,
                'event_id' => $event->eventId,
                'event_type' => $event->type->value,
                'case_id' => $event->caseId,
                'aggregate_version' => $event->aggregateVersion,
                'transport' => $transport,
                'checksum' => $event->checksum,
                'correlation_id' => $event->correlationId,
                'causation_id' => $event->causationId,
                'occurred_at' => $event->occurredAt->format('Y-m-d H:i:s.uP'),
                'recorded_at' => $event->recordedAt->format('Y-m-d H:i:s.uP'),
            ]);
            $created = $insert->rowCount() === 1;
            if (! $created && ! $this->matches($message)) {
                return ModerationOutboxAppendResult::DivergentMessage;
            }

            $delivery = $this->connection->prepare(
                "INSERT INTO moderation_reports.outbox_deliveries(
                    message_id,destination,status,attempts
                 ) VALUES (:message_id,:destination,'Pending',0)
                 ON CONFLICT(message_id,destination) DO NOTHING",
            );
            foreach ($this->router->route($event) as $destination) {
                $delivery->execute([
                    'message_id' => $message->messageId,
                    'destination' => $destination->value,
                ]);
            }

            return $created
                ? ModerationOutboxAppendResult::Stored
                : ModerationOutboxAppendResult::AlreadyStored;
        });
    }

    public function read(string $messageId, string $destination): ?ModerationOutboxDelivery
    {
        $statement = $this->connection->prepare(
            'SELECT d.message_id,d.destination,d.attempts,d.claim_owner,m.canonical_transport::text
             FROM moderation_reports.outbox_deliveries d
             JOIN moderation_reports.outbox_messages m USING(message_id)
             WHERE d.message_id=:message_id AND d.destination=:destination',
        );
        $statement->execute(['message_id' => $messageId, 'destination' => $destination]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->delivery($row) : null;
    }

    public function claimNext(string $owner, DateTimeImmutable $now): ?ModerationOutboxDelivery
    {
        return $this->claim($owner, null, $now);
    }

    public function claimNextForDestination(
        string $owner,
        ModerationRoutingDestination $destination,
        DateTimeImmutable $now,
    ): ?ModerationOutboxDelivery {
        return $this->claim($owner, $destination, $now);
    }

    private function claim(
        string $owner,
        ?ModerationRoutingDestination $destination,
        DateTimeImmutable $now,
    ): ?ModerationOutboxDelivery {
        if (preg_match('/^[a-zA-Z0-9._-]{1,96}$/', $owner) !== 1) {
            throw new InvalidArgumentException('Invalid Moderation Outbox claim owner.');
        }

        return $this->transaction(function () use ($owner, $destination, $now): ?ModerationOutboxDelivery {
            $destinationFilter = $destination === null ? '' : 'AND d.destination=:destination';
            $statement = $this->connection->prepare(
                "SELECT d.message_id,d.destination,d.attempts,m.canonical_transport::text
                 FROM moderation_reports.outbox_deliveries d
                 JOIN moderation_reports.outbox_messages m USING(message_id)
                 WHERE (d.status='Pending'
                    OR (d.status='Retry' AND d.available_at<=CAST(:now AS timestamptz))
                    OR (d.status='Claimed' AND d.claimed_until<=CAST(:now AS timestamptz)))
                 {$destinationFilter}
                 ORDER BY d.available_at,d.message_id,d.destination
                 FOR UPDATE OF d SKIP LOCKED LIMIT 1",
            );
            $parameters = ['now' => $now->format('Y-m-d H:i:s.uP')];
            if ($destination !== null) {
                $parameters['destination'] = $destination->value;
            }
            $statement->execute($parameters);
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            if (! is_array($row)) {
                return null;
            }
            $claim = $this->connection->prepare(
                "UPDATE moderation_reports.outbox_deliveries
                 SET status='Claimed',attempts=attempts+1,claim_owner=:owner,
                     claimed_until=CAST(:until AS timestamptz)
                 WHERE message_id=:message_id AND destination=:destination",
            );
            $claim->execute([
                'owner' => $owner,
                'until' => $now->modify('+30 seconds')->format('Y-m-d H:i:s.uP'),
                'message_id' => $row['message_id'],
                'destination' => $row['destination'],
            ]);
            $row['attempts'] = ((int) $row['attempts']) + 1;
            $row['claim_owner'] = $owner;

            return $this->delivery($row);
        });
    }

    public function release(ModerationOutboxDelivery $delivery, DateTimeImmutable $availableAt): bool
    {
        return $this->claimedUpdate(
            $delivery,
            "status='Pending',claim_owner=NULL,claimed_until=NULL,available_at=CAST(:value AS timestamptz)",
            $availableAt->format('Y-m-d H:i:s.uP'),
        );
    }

    public function retry(
        ModerationOutboxDelivery $delivery,
        DateTimeImmutable $availableAt,
        string $errorCode,
    ): bool {
        return $this->claimedUpdate(
            $delivery,
            "status='Retry',claim_owner=NULL,claimed_until=NULL,available_at=CAST(:value AS timestamptz),last_error_code=:error",
            $availableAt->format('Y-m-d H:i:s.uP'),
            $errorCode,
        );
    }

    public function markDelivered(ModerationOutboxDelivery $delivery, DateTimeImmutable $at): bool
    {
        return $this->claimedUpdate(
            $delivery,
            "status='Delivered',claim_owner=NULL,claimed_until=NULL,delivered_at=CAST(:value AS timestamptz),last_error_code=NULL",
            $at->format('Y-m-d H:i:s.uP'),
        );
    }

    public function quarantine(ModerationOutboxDelivery $delivery, string $errorCode): bool
    {
        return $this->claimedUpdate(
            $delivery,
            "status='Quarantined',claim_owner=NULL,claimed_until=NULL,last_error_code=:error",
            '',
            $errorCode,
        );
    }

    public function replay(string $messageId, string $destination, DateTimeImmutable $at): bool
    {
        $statement = $this->connection->prepare(
            "UPDATE moderation_reports.outbox_deliveries
             SET status='Pending',attempts=0,available_at=CAST(:at AS timestamptz),
                 claim_owner=NULL,claimed_until=NULL,delivered_at=NULL,last_error_code=NULL
             WHERE message_id=:message_id AND destination=:destination
               AND status IN ('Delivered','Quarantined')",
        );
        $statement->execute([
            'message_id' => $messageId,
            'destination' => $destination,
            'at' => $at->format('Y-m-d H:i:s.uP'),
        ]);

        return $statement->rowCount() === 1;
    }

    private function matches(ModerationDeliveryMessageV1 $message): bool
    {
        $statement = $this->connection->prepare(
            'SELECT message_id,event_id::text,payload_checksum
             FROM moderation_reports.outbox_messages
             WHERE message_id=:message_id OR event_id=CAST(:event_id AS uuid)',
        );
        $statement->execute([
            'message_id' => $message->messageId,
            'event_id' => $message->event->eventId,
        ]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row)
            && hash_equals((string) $row['message_id'], $message->messageId)
            && hash_equals((string) $row['event_id'], $message->event->eventId)
            && hash_equals((string) $row['payload_checksum'], $message->event->checksum);
    }

    /** @param array<string, mixed> $row */
    private function delivery(array $row): ModerationOutboxDelivery
    {
        return new ModerationOutboxDelivery(
            $this->restore((string) $row['canonical_transport']),
            ModerationRoutingDestination::from((string) $row['destination']),
            (int) $row['attempts'],
            (string) ($row['claim_owner'] ?? ''),
        );
    }

    private function restore(string $transport): ModerationDeliveryMessageV1
    {
        $fields = json_decode($transport, true, flags: JSON_THROW_ON_ERROR);
        if (! is_array($fields) || ! is_array($fields['payload'] ?? null)) {
            throw new InvalidArgumentException('Invalid Moderation Outbox transport.');
        }
        $contract = $fields['payload'];
        $payload = $contract['payload'] ?? null;
        if (! is_array($payload)) {
            throw new InvalidArgumentException('Invalid Moderation Outbox payload.');
        }
        $message = new ModerationDeliveryMessageV1(new ModerationEventV1(
            ModerationEventTypeV1::from((string) $contract['eventType']),
            (string) $contract['caseId'],
            (int) $contract['aggregateVersion'],
            $payload,
            (string) $contract['policyVersion'],
            new DateTimeImmutable((string) $contract['occurredAt']),
            new DateTimeImmutable((string) $contract['recordedAt']),
            (string) $contract['correlationId'],
            (string) $contract['causationId'],
        ));
        if (
            ! hash_equals((string) ($fields['messageId'] ?? ''), $message->messageId)
            || ! hash_equals((string) ($contract['eventId'] ?? ''), $message->event->eventId)
            || ! hash_equals((string) ($contract['checksum'] ?? ''), $message->event->checksum)
        ) {
            throw new InvalidArgumentException('Corrupted Moderation Outbox transport.');
        }

        return $message;
    }

    private function claimedUpdate(
        ModerationOutboxDelivery $delivery,
        string $set,
        string $value,
        ?string $error = null,
    ): bool {
        $statement = $this->connection->prepare(
            "UPDATE moderation_reports.outbox_deliveries SET {$set}
             WHERE message_id=:message_id AND destination=:destination
               AND status='Claimed' AND claim_owner=:owner",
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

    /** @template T
     * @param  callable(): T  $operation
     * @return T
     */
    private function transaction(callable $operation): mixed
    {
        $owner = ! $this->connection->inTransaction();
        $savepoint = 'moderation_outbox';
        $owner
            ? $this->connection->beginTransaction()
            : $this->connection->exec("SAVEPOINT {$savepoint}");
        try {
            $result = $operation();
            $owner
                ? $this->connection->commit()
                : $this->connection->exec("RELEASE SAVEPOINT {$savepoint}");

            return $result;
        } catch (Throwable $error) {
            $owner
                ? $this->connection->rollBack()
                : $this->connection->exec("ROLLBACK TO SAVEPOINT {$savepoint}");
            throw $error;
        }
    }
}

<?php

namespace App\Infrastructure\ModerationEventDelivery\PostgreSql;

use App\Application\ModerationEventDelivery\Contract\ModerationDeliveryStore;
use App\Application\ModerationEventDelivery\ModerationDeliveryResult;
use App\Application\ModerationEventRouting\ModerationRoutingDestination;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use DateTimeImmutable;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlModerationDeliveryStore implements ModerationDeliveryStore
{
    public function __construct(private PDO $connection) {}

    public function deliver(ModerationDeliveryMessageV1 $message, ModerationRoutingDestination $destination, DateTimeImmutable $at): ModerationDeliveryResult
    {
        return $this->write($message, $destination, 0, $at, true);
    }

    public function fail(ModerationDeliveryMessageV1 $message, ModerationRoutingDestination $destination, int $attempt, DateTimeImmutable $at): ModerationDeliveryResult
    {
        return $this->write($message, $destination, $attempt, $at, false);
    }

    private function write(ModerationDeliveryMessageV1 $message, ModerationRoutingDestination $destination, int $attempt, DateTimeImmutable $at, bool $delivered): ModerationDeliveryResult
    {
        try {
            return $this->transaction(function () use ($message, $destination, $attempt, $at, $delivered): ModerationDeliveryResult {
                $identity = $message->event->eventId.'|'.$destination->value;
                $lock = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:identity,0))');
                $lock->execute(['identity' => $identity]);
                $read = $this->connection->prepare(
                    'SELECT event_checksum,status FROM moderation_reports.event_deliveries
                     WHERE event_id=CAST(:event_id AS uuid) AND destination=:destination',
                );
                $read->execute(['event_id' => $message->event->eventId, 'destination' => $destination->value]);
                $row = $read->fetch(PDO::FETCH_ASSOC);
                if ($row !== false && ! hash_equals((string) $row['event_checksum'], $message->event->checksum)) {
                    return ModerationDeliveryResult::DivergentDelivery;
                }
                if ($row !== false && $row['status'] === 'Delivered') {
                    return ModerationDeliveryResult::AlreadyDelivered;
                }
                $status = $delivered ? 'Delivered' : ($attempt >= 5 ? 'Quarantined' : 'Retry');
                $statement = $this->connection->prepare(
                    'INSERT INTO moderation_reports.event_deliveries(event_id,destination,event_checksum,message_id,status,attempts,updated_at)
                     VALUES(CAST(:event_id AS uuid),:destination,:checksum,:message_id,:status,:attempts,CAST(:updated_at AS timestamptz))
                     ON CONFLICT(event_id,destination) DO UPDATE SET status=EXCLUDED.status,attempts=EXCLUDED.attempts,updated_at=EXCLUDED.updated_at',
                );
                $statement->execute([
                    'event_id' => $message->event->eventId, 'destination' => $destination->value,
                    'checksum' => $message->event->checksum, 'message_id' => $message->messageId,
                    'status' => $status, 'attempts' => min(5, max(0, $attempt)),
                    'updated_at' => $at->format('Y-m-d H:i:s.uP'),
                ]);

                return match ($status) {
                    'Delivered' => ModerationDeliveryResult::Delivered,
                    'Retry' => ModerationDeliveryResult::Retry,
                    default => ModerationDeliveryResult::Quarantined,
                };
            });
        } catch (PDOException) {
            return ModerationDeliveryResult::Rejected;
        }
    }

    /**
     * @template T
     *
     * @param  callable(): T  $operation
     * @return T
     */
    private function transaction(callable $operation): mixed
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        } else {
            $this->connection->exec('SAVEPOINT moderation_event_delivery');
        }
        try {
            $result = $operation();
            $owner ? $this->connection->commit() : $this->connection->exec('RELEASE SAVEPOINT moderation_event_delivery');

            return $result;
        } catch (Throwable $error) {
            $owner ? $this->connection->rollBack() : $this->connection->exec('ROLLBACK TO SAVEPOINT moderation_event_delivery');
            throw $error;
        }
    }
}

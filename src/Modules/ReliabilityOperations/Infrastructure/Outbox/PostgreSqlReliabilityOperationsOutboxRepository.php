<?php

namespace Appart\Modules\ReliabilityOperations\Infrastructure\Outbox;

use Appart\Modules\ReliabilityOperations\Application\Delivery\AlertingDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Delivery\CapacityPlanningDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ContinuityDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Delivery\MaintenanceOperationsDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ObservabilityDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Delivery\OperationalReadinessDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ServiceHealthDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxAppendResult;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxClaimResult;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxMessage;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxMessageId;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxMessageStatus;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxPolicy;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxRetryResult;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxStore;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final readonly class PostgreSqlReliabilityOperationsOutboxRepository implements ReliabilityOperationsOutboxStore
{
    public function __construct(private PDO $connection, private ReliabilityOperationsOutboxMapper $mapper, private ReliabilityOperationsOutboxPolicy $policy) {}

    public function append(ObservabilityDeliveryV1|ServiceHealthDeliveryV1|AlertingDeliveryV1|MaintenanceOperationsDeliveryV1|ContinuityDeliveryV1|CapacityPlanningDeliveryV1|OperationalReadinessDeliveryV1 $delivery, DateTimeImmutable $createdAt): ReliabilityOperationsOutboxAppendResult
    {
        $message = $this->mapper->fromDelivery($delivery, $createdAt);
        try {
            return $this->transactional(function () use ($message): ReliabilityOperationsOutboxAppendResult {
                $payload = json_encode($message->payload(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
                $insert = $this->connection->prepare('INSERT INTO reliability_operations.outbox_message_journal(message_id,event_id,owner_name,schema_version,message_type,delivery_status,observed_at,payload,message_checksum,created_at) VALUES(:message_id,:event_id,:owner_name,:schema_version,:message_type,:delivery_status,CAST(:observed_at AS timestamptz),CAST(:payload AS jsonb),:message_checksum,CAST(:created_at AS timestamptz)) ON CONFLICT DO NOTHING');
                $insert->execute([
                    'message_id' => $message->messageId->value,
                    'event_id' => $message->eventId->value,
                    'owner_name' => ReliabilityOperationsOutboxMessage::OWNER,
                    'schema_version' => ReliabilityOperationsOutboxMessage::SCHEMA_VERSION,
                    'message_type' => $message->type->value,
                    'delivery_status' => $message->deliveryStatus,
                    'observed_at' => $message->observedAt,
                    'payload' => $payload,
                    'message_checksum' => $message->checksum,
                    'created_at' => $this->canonical($message->createdAt),
                ]);
                if ($insert->rowCount() === 1) {
                    $state = $this->connection->prepare('INSERT INTO reliability_operations.outbox_message_state(message_id,technical_status,attempts,available_at) VALUES(:message_id,:technical_status,0,CAST(:available_at AS timestamptz))');
                    $state->execute(['message_id' => $message->messageId->value, 'technical_status' => ReliabilityOperationsOutboxMessageStatus::Pending->value, 'available_at' => $this->canonical($message->availableAt)]);

                    return ReliabilityOperationsOutboxAppendResult::Applied;
                }

                $existing = $this->connection->prepare('SELECT message_id,message_checksum,payload::text FROM reliability_operations.outbox_message_journal WHERE event_id=:event_id FOR UPDATE');
                $existing->execute(['event_id' => $message->eventId->value]);
                $row = $existing->fetch(PDO::FETCH_ASSOC);
                if (! is_array($row)) {
                    return ReliabilityOperationsOutboxAppendResult::DivergentMessage;
                }
                $same = hash_equals((string) $row['message_id'], $message->messageId->value)
                    && hash_equals((string) $row['message_checksum'], $message->checksum)
                    && json_decode((string) $row['payload'], true, 512, JSON_THROW_ON_ERROR) === $message->payload();

                return $same ? ReliabilityOperationsOutboxAppendResult::AlreadyApplied : ReliabilityOperationsOutboxAppendResult::DivergentMessage;
            });
        } catch (PDOException) {
            return ReliabilityOperationsOutboxAppendResult::DependencyUnavailable;
        }
    }

    public function eligible(DateTimeImmutable $availableAt, int $limit): array
    {
        if (! $this->policy->acceptsReadLimit($limit)) {
            throw new RuntimeException('ReliabilityOperations outbox limit must be between 1 and 100.');
        }
        $query = $this->connection->prepare($this->selectSql()." WHERE s.technical_status IN ('pending','retry_scheduled') AND s.available_at<=CAST(:available_at AS timestamptz) AND s.attempts<:max_attempts ORDER BY s.available_at,j.created_at,j.message_id LIMIT :limit");
        $query->bindValue('available_at', $this->canonical($availableAt));
        $query->bindValue('max_attempts', ReliabilityOperationsOutboxPolicy::MAX_ATTEMPTS, PDO::PARAM_INT);
        $query->bindValue('limit', $limit, PDO::PARAM_INT);
        $query->execute();
        /** @var list<array{message_id:string,event_id:string,message_type:string,delivery_status:string,observed_at:string,message_checksum:string,created_at:string,available_at:string,attempts:int|string,technical_status:string}> $rows */
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);

        return array_map($this->mapper->fromRow(...), $rows);
    }

    public function claim(ReliabilityOperationsOutboxMessageId $messageId, DateTimeImmutable $claimedAt): ReliabilityOperationsOutboxClaimResult
    {
        try {
            return $this->transactional(function () use ($messageId, $claimedAt): ReliabilityOperationsOutboxClaimResult {
                if (! $this->exists($messageId)) {
                    return ReliabilityOperationsOutboxClaimResult::Missing;
                }
                $row = $this->lockedRow($messageId);
                if ($row === false) {
                    return ReliabilityOperationsOutboxClaimResult::AlreadyClaimed;
                }
                try {
                    $message = $this->mapper->fromRow($row);
                } catch (Throwable) {
                    return ReliabilityOperationsOutboxClaimResult::Corrupted;
                }
                if ($message->status === ReliabilityOperationsOutboxMessageStatus::Completed) {
                    return ReliabilityOperationsOutboxClaimResult::AlreadyCompleted;
                }
                if ($message->status === ReliabilityOperationsOutboxMessageStatus::Claimed) {
                    return ReliabilityOperationsOutboxClaimResult::AlreadyClaimed;
                }
                if ($message->attempts >= ReliabilityOperationsOutboxPolicy::MAX_ATTEMPTS) {
                    $this->exhaust($messageId);

                    return ReliabilityOperationsOutboxClaimResult::AttemptsExhausted;
                }
                if ($message->availableAt > $claimedAt) {
                    return ReliabilityOperationsOutboxClaimResult::AlreadyClaimed;
                }
                $update = $this->connection->prepare("UPDATE reliability_operations.outbox_message_state SET technical_status='claimed',attempts=attempts+1,claimed_at=CAST(:claimed_at AS timestamptz) WHERE message_id=:message_id");
                $update->execute(['claimed_at' => $this->canonical($claimedAt), 'message_id' => $messageId->value]);

                return ReliabilityOperationsOutboxClaimResult::Claimed;
            });
        } catch (PDOException) {
            return ReliabilityOperationsOutboxClaimResult::DependencyUnavailable;
        }
    }

    public function retry(ReliabilityOperationsOutboxMessageId $messageId, DateTimeImmutable $availableAt): ReliabilityOperationsOutboxRetryResult
    {
        try {
            return $this->transactional(function () use ($messageId, $availableAt): ReliabilityOperationsOutboxRetryResult {
                $row = $this->lockedRow($messageId);
                if ($row === false) {
                    return ReliabilityOperationsOutboxRetryResult::Missing;
                }
                try {
                    $message = $this->mapper->fromRow($row);
                } catch (Throwable) {
                    return ReliabilityOperationsOutboxRetryResult::Corrupted;
                }
                if ($message->status === ReliabilityOperationsOutboxMessageStatus::Completed) {
                    return ReliabilityOperationsOutboxRetryResult::AlreadyCompleted;
                }
                if ($message->attempts >= ReliabilityOperationsOutboxPolicy::MAX_ATTEMPTS) {
                    $this->exhaust($messageId);

                    return ReliabilityOperationsOutboxRetryResult::AttemptsExhausted;
                }
                $update = $this->connection->prepare("UPDATE reliability_operations.outbox_message_state SET technical_status='retry_scheduled',available_at=CAST(:available_at AS timestamptz),claimed_at=NULL WHERE message_id=:message_id");
                $update->execute(['available_at' => $this->canonical($availableAt), 'message_id' => $messageId->value]);

                return ReliabilityOperationsOutboxRetryResult::RetryScheduled;
            });
        } catch (PDOException) {
            return ReliabilityOperationsOutboxRetryResult::DependencyUnavailable;
        }
    }

    /** @return array{message_id:string,event_id:string,message_type:string,delivery_status:string,observed_at:string,message_checksum:string,created_at:string,available_at:string,attempts:int|string,technical_status:string}|false */
    private function lockedRow(ReliabilityOperationsOutboxMessageId $messageId): array|false
    {
        $query = $this->connection->prepare($this->selectSql().' WHERE j.message_id=:message_id FOR UPDATE OF s SKIP LOCKED');
        $query->execute(['message_id' => $messageId->value]);

        /** @var array{message_id:string,event_id:string,message_type:string,delivery_status:string,observed_at:string,message_checksum:string,created_at:string,available_at:string,attempts:int|string,technical_status:string}|false */
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    private function exhaust(ReliabilityOperationsOutboxMessageId $messageId): void
    {
        $query = $this->connection->prepare("UPDATE reliability_operations.outbox_message_state SET technical_status='attempts_exhausted',claimed_at=NULL WHERE message_id=:message_id");
        $query->execute(['message_id' => $messageId->value]);
    }

    private function exists(ReliabilityOperationsOutboxMessageId $messageId): bool
    {
        $query = $this->connection->prepare('SELECT 1 FROM reliability_operations.outbox_message_state WHERE message_id=:message_id');
        $query->execute(['message_id' => $messageId->value]);

        return $query->fetchColumn() !== false;
    }

    private function selectSql(): string
    {
        return 'SELECT j.message_id,j.event_id,j.message_type,j.delivery_status,j.observed_at,j.message_checksum,j.created_at,s.available_at,s.attempts,s.technical_status FROM reliability_operations.outbox_message_journal j JOIN reliability_operations.outbox_message_state s ON s.message_id=j.message_id';
    }

    private function transactional(callable $operation): mixed
    {
        $owner = ! $this->connection->inTransaction();
        $owner ? $this->connection->beginTransaction() : $this->connection->exec('SAVEPOINT '.ReliabilityOperationsOutboxPolicy::SAVEPOINT);
        try {
            $result = $operation();
            $owner ? $this->connection->commit() : $this->connection->exec('RELEASE SAVEPOINT '.ReliabilityOperationsOutboxPolicy::SAVEPOINT);

            return $result;
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            } else {
                $this->connection->exec('ROLLBACK TO SAVEPOINT '.ReliabilityOperationsOutboxPolicy::SAVEPOINT);
                $this->connection->exec('RELEASE SAVEPOINT '.ReliabilityOperationsOutboxPolicy::SAVEPOINT);
            }
            throw $error;
        }
    }

    private function canonical(DateTimeImmutable $instant): string
    {
        return $instant->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}

<?php

namespace Appart\Modules\ExperienceAcceptance\Infrastructure\Outbox;

use Appart\Modules\ExperienceAcceptance\Application\Delivery\AccessibilityComplianceDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\EndToEndReadinessDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\PerformanceReadinessDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\ReleaseCandidateDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\ResponsiveComplianceDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\UserAcceptanceDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\UserExperienceDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxAppendResult;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxClaimResult;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessage;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessageId;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessageStatus;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxPolicy;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxRetryResult;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxStore;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final readonly class PostgreSqlExperienceAcceptanceOutboxRepository implements ExperienceAcceptanceOutboxStore
{
    public function __construct(private PDO $connection, private ExperienceAcceptanceOutboxMapper $mapper, private ExperienceAcceptanceOutboxPolicy $policy) {}

    public function append(ResponsiveComplianceDeliveryV1|AccessibilityComplianceDeliveryV1|UserExperienceDeliveryV1|EndToEndReadinessDeliveryV1|PerformanceReadinessDeliveryV1|UserAcceptanceDeliveryV1|ReleaseCandidateDeliveryV1 $delivery, DateTimeImmutable $createdAt): ExperienceAcceptanceOutboxAppendResult
    {
        $message = $this->mapper->fromDelivery($delivery, $createdAt);
        try {
            return $this->transactional(function () use ($message): ExperienceAcceptanceOutboxAppendResult {
                $payload = json_encode($message->payload(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
                $insert = $this->connection->prepare('INSERT INTO experience_acceptance.outbox_message_journal(message_id,event_id,owner_name,schema_version,message_type,delivery_status,observed_at,payload,message_checksum,created_at) VALUES(:message_id,:event_id,:owner_name,:schema_version,:message_type,:delivery_status,CAST(:observed_at AS timestamptz),CAST(:payload AS jsonb),:message_checksum,CAST(:created_at AS timestamptz)) ON CONFLICT DO NOTHING');
                $insert->execute([
                    'message_id' => $message->messageId->value,
                    'event_id' => $message->eventId->value,
                    'owner_name' => ExperienceAcceptanceOutboxMessage::OWNER,
                    'schema_version' => ExperienceAcceptanceOutboxMessage::SCHEMA_VERSION,
                    'message_type' => $message->type->value,
                    'delivery_status' => $message->deliveryStatus,
                    'observed_at' => $message->observedAt,
                    'payload' => $payload,
                    'message_checksum' => $message->checksum,
                    'created_at' => $this->canonical($message->createdAt),
                ]);
                if ($insert->rowCount() === 1) {
                    $state = $this->connection->prepare('INSERT INTO experience_acceptance.outbox_message_state(message_id,technical_status,attempts,available_at) VALUES(:message_id,:technical_status,0,CAST(:available_at AS timestamptz))');
                    $state->execute(['message_id' => $message->messageId->value, 'technical_status' => ExperienceAcceptanceOutboxMessageStatus::Pending->value, 'available_at' => $this->canonical($message->availableAt)]);

                    return ExperienceAcceptanceOutboxAppendResult::Applied;
                }

                $existing = $this->connection->prepare('SELECT message_id,message_checksum,payload::text FROM experience_acceptance.outbox_message_journal WHERE event_id=:event_id FOR UPDATE');
                $existing->execute(['event_id' => $message->eventId->value]);
                $row = $existing->fetch(PDO::FETCH_ASSOC);
                if (! is_array($row)) {
                    return ExperienceAcceptanceOutboxAppendResult::DivergentMessage;
                }
                $same = hash_equals((string) $row['message_id'], $message->messageId->value)
                    && hash_equals((string) $row['message_checksum'], $message->checksum)
                    && json_decode((string) $row['payload'], true, 512, JSON_THROW_ON_ERROR) === $message->payload();

                return $same ? ExperienceAcceptanceOutboxAppendResult::AlreadyApplied : ExperienceAcceptanceOutboxAppendResult::DivergentMessage;
            });
        } catch (PDOException) {
            return ExperienceAcceptanceOutboxAppendResult::DependencyUnavailable;
        }
    }

    public function eligible(DateTimeImmutable $availableAt, int $limit): array
    {
        if (! $this->policy->acceptsReadLimit($limit)) {
            throw new RuntimeException('ExperienceAcceptance outbox limit must be between 1 and 100.');
        }
        $query = $this->connection->prepare($this->selectSql()." WHERE s.technical_status IN ('pending','retry_scheduled') AND s.available_at<=CAST(:available_at AS timestamptz) AND s.attempts<:max_attempts ORDER BY s.available_at,j.created_at,j.message_id LIMIT :limit");
        $query->bindValue('available_at', $this->canonical($availableAt));
        $query->bindValue('max_attempts', ExperienceAcceptanceOutboxPolicy::MAX_ATTEMPTS, PDO::PARAM_INT);
        $query->bindValue('limit', $limit, PDO::PARAM_INT);
        $query->execute();
        /** @var list<array{message_id:string,event_id:string,message_type:string,delivery_status:string,observed_at:string,message_checksum:string,created_at:string,available_at:string,attempts:int|string,technical_status:string}> $rows */
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);

        return array_map($this->mapper->fromRow(...), $rows);
    }

    public function claim(ExperienceAcceptanceOutboxMessageId $messageId, DateTimeImmutable $claimedAt): ExperienceAcceptanceOutboxClaimResult
    {
        try {
            return $this->transactional(function () use ($messageId, $claimedAt): ExperienceAcceptanceOutboxClaimResult {
                if (! $this->exists($messageId)) {
                    return ExperienceAcceptanceOutboxClaimResult::Missing;
                }
                $row = $this->lockedRow($messageId);
                if ($row === false) {
                    return ExperienceAcceptanceOutboxClaimResult::AlreadyClaimed;
                }
                try {
                    $message = $this->mapper->fromRow($row);
                } catch (Throwable) {
                    return ExperienceAcceptanceOutboxClaimResult::Corrupted;
                }
                if ($message->status === ExperienceAcceptanceOutboxMessageStatus::Completed) {
                    return ExperienceAcceptanceOutboxClaimResult::AlreadyCompleted;
                }
                if ($message->status === ExperienceAcceptanceOutboxMessageStatus::Claimed) {
                    return ExperienceAcceptanceOutboxClaimResult::AlreadyClaimed;
                }
                if ($message->attempts >= ExperienceAcceptanceOutboxPolicy::MAX_ATTEMPTS) {
                    $this->exhaust($messageId);

                    return ExperienceAcceptanceOutboxClaimResult::AttemptsExhausted;
                }
                if ($message->availableAt > $claimedAt) {
                    return ExperienceAcceptanceOutboxClaimResult::AlreadyClaimed;
                }
                $update = $this->connection->prepare("UPDATE experience_acceptance.outbox_message_state SET technical_status='claimed',attempts=attempts+1,claimed_at=CAST(:claimed_at AS timestamptz) WHERE message_id=:message_id");
                $update->execute(['claimed_at' => $this->canonical($claimedAt), 'message_id' => $messageId->value]);

                return ExperienceAcceptanceOutboxClaimResult::Claimed;
            });
        } catch (PDOException) {
            return ExperienceAcceptanceOutboxClaimResult::DependencyUnavailable;
        }
    }

    public function retry(ExperienceAcceptanceOutboxMessageId $messageId, DateTimeImmutable $availableAt): ExperienceAcceptanceOutboxRetryResult
    {
        try {
            return $this->transactional(function () use ($messageId, $availableAt): ExperienceAcceptanceOutboxRetryResult {
                $row = $this->lockedRow($messageId);
                if ($row === false) {
                    return ExperienceAcceptanceOutboxRetryResult::Missing;
                }
                try {
                    $message = $this->mapper->fromRow($row);
                } catch (Throwable) {
                    return ExperienceAcceptanceOutboxRetryResult::Corrupted;
                }
                if ($message->status === ExperienceAcceptanceOutboxMessageStatus::Completed) {
                    return ExperienceAcceptanceOutboxRetryResult::AlreadyCompleted;
                }
                if ($message->attempts >= ExperienceAcceptanceOutboxPolicy::MAX_ATTEMPTS) {
                    $this->exhaust($messageId);

                    return ExperienceAcceptanceOutboxRetryResult::AttemptsExhausted;
                }
                $update = $this->connection->prepare("UPDATE experience_acceptance.outbox_message_state SET technical_status='retry_scheduled',available_at=CAST(:available_at AS timestamptz),claimed_at=NULL WHERE message_id=:message_id");
                $update->execute(['available_at' => $this->canonical($availableAt), 'message_id' => $messageId->value]);

                return ExperienceAcceptanceOutboxRetryResult::RetryScheduled;
            });
        } catch (PDOException) {
            return ExperienceAcceptanceOutboxRetryResult::DependencyUnavailable;
        }
    }

    /** @return array{message_id:string,event_id:string,message_type:string,delivery_status:string,observed_at:string,message_checksum:string,created_at:string,available_at:string,attempts:int|string,technical_status:string}|false */
    private function lockedRow(ExperienceAcceptanceOutboxMessageId $messageId): array|false
    {
        $query = $this->connection->prepare($this->selectSql().' WHERE j.message_id=:message_id FOR UPDATE OF s SKIP LOCKED');
        $query->execute(['message_id' => $messageId->value]);

        /** @var array{message_id:string,event_id:string,message_type:string,delivery_status:string,observed_at:string,message_checksum:string,created_at:string,available_at:string,attempts:int|string,technical_status:string}|false */
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    private function exhaust(ExperienceAcceptanceOutboxMessageId $messageId): void
    {
        $query = $this->connection->prepare("UPDATE experience_acceptance.outbox_message_state SET technical_status='attempts_exhausted',claimed_at=NULL WHERE message_id=:message_id");
        $query->execute(['message_id' => $messageId->value]);
    }

    private function exists(ExperienceAcceptanceOutboxMessageId $messageId): bool
    {
        $query = $this->connection->prepare('SELECT 1 FROM experience_acceptance.outbox_message_state WHERE message_id=:message_id');
        $query->execute(['message_id' => $messageId->value]);

        return $query->fetchColumn() !== false;
    }

    private function selectSql(): string
    {
        return 'SELECT j.message_id,j.event_id,j.message_type,j.delivery_status,j.observed_at,j.message_checksum,j.created_at,s.available_at,s.attempts,s.technical_status FROM experience_acceptance.outbox_message_journal j JOIN experience_acceptance.outbox_message_state s ON s.message_id=j.message_id';
    }

    private function transactional(callable $operation): mixed
    {
        $owner = ! $this->connection->inTransaction();
        $owner ? $this->connection->beginTransaction() : $this->connection->exec('SAVEPOINT '.ExperienceAcceptanceOutboxPolicy::SAVEPOINT);
        try {
            $result = $operation();
            $owner ? $this->connection->commit() : $this->connection->exec('RELEASE SAVEPOINT '.ExperienceAcceptanceOutboxPolicy::SAVEPOINT);

            return $result;
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            } else {
                $this->connection->exec('ROLLBACK TO SAVEPOINT '.ExperienceAcceptanceOutboxPolicy::SAVEPOINT);
                $this->connection->exec('RELEASE SAVEPOINT '.ExperienceAcceptanceOutboxPolicy::SAVEPOINT);
            }
            throw $error;
        }
    }

    private function canonical(DateTimeImmutable $instant): string
    {
        return $instant->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}

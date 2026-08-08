<?php

namespace Appart\Modules\SecurityCompliance\Infrastructure\Outbox;

use Appart\Modules\SecurityCompliance\Application\Delivery\ComplianceControlDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Delivery\IncidentDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Delivery\PrivacyPolicyDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Delivery\SecretInventoryDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Delivery\SecurityAuditDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Outbox\SecurityComplianceOutboxAppendResult;
use Appart\Modules\SecurityCompliance\Application\Outbox\SecurityComplianceOutboxClaimResult;
use Appart\Modules\SecurityCompliance\Application\Outbox\SecurityComplianceOutboxMessage;
use Appart\Modules\SecurityCompliance\Application\Outbox\SecurityComplianceOutboxMessageId;
use Appart\Modules\SecurityCompliance\Application\Outbox\SecurityComplianceOutboxMessageStatus;
use Appart\Modules\SecurityCompliance\Application\Outbox\SecurityComplianceOutboxRetryResult;
use Appart\Modules\SecurityCompliance\Application\Outbox\SecurityComplianceOutboxStore;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final readonly class PostgreSqlSecurityComplianceOutboxRepository implements SecurityComplianceOutboxStore
{
    private const SAVEPOINT = 'security_compliance_outbox';

    private const MAX_ATTEMPTS = 10;

    public function __construct(private PDO $connection, private SecurityComplianceOutboxMapper $mapper) {}

    public function append(SecretInventoryDeliveryV1|SecurityAuditDeliveryV1|IncidentDeliveryV1|PrivacyPolicyDeliveryV1|ComplianceControlDeliveryV1 $delivery, DateTimeImmutable $createdAt): SecurityComplianceOutboxAppendResult
    {
        $message = $this->mapper->fromDelivery($delivery, $createdAt);
        try {
            return $this->transactional(function () use ($message): SecurityComplianceOutboxAppendResult {
                $payload = json_encode($message->payload(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
                $insert = $this->connection->prepare('INSERT INTO security_compliance.outbox_message_journal(message_id,event_id,owner_name,schema_version,message_type,delivery_status,observed_at,payload,message_checksum,created_at) VALUES(:message_id,:event_id,:owner_name,:schema_version,:message_type,:delivery_status,CAST(:observed_at AS timestamptz),CAST(:payload AS jsonb),:message_checksum,CAST(:created_at AS timestamptz)) ON CONFLICT DO NOTHING');
                $insert->execute([
                    'message_id' => $message->messageId->value,
                    'event_id' => $message->eventId->value,
                    'owner_name' => SecurityComplianceOutboxMessage::OWNER,
                    'schema_version' => SecurityComplianceOutboxMessage::SCHEMA_VERSION,
                    'message_type' => $message->type->value,
                    'delivery_status' => $message->deliveryStatus,
                    'observed_at' => $message->observedAt,
                    'payload' => $payload,
                    'message_checksum' => $message->checksum,
                    'created_at' => $this->canonical($message->createdAt),
                ]);
                if ($insert->rowCount() === 1) {
                    $state = $this->connection->prepare('INSERT INTO security_compliance.outbox_message_state(message_id,technical_status,attempts,available_at) VALUES(:message_id,:technical_status,0,CAST(:available_at AS timestamptz))');
                    $state->execute(['message_id' => $message->messageId->value, 'technical_status' => SecurityComplianceOutboxMessageStatus::Pending->value, 'available_at' => $this->canonical($message->availableAt)]);

                    return SecurityComplianceOutboxAppendResult::Applied;
                }

                $existing = $this->connection->prepare('SELECT message_id,message_checksum,payload::text FROM security_compliance.outbox_message_journal WHERE event_id=:event_id FOR UPDATE');
                $existing->execute(['event_id' => $message->eventId->value]);
                $row = $existing->fetch(PDO::FETCH_ASSOC);
                if (! is_array($row)) {
                    return SecurityComplianceOutboxAppendResult::DivergentMessage;
                }
                $same = hash_equals((string) $row['message_id'], $message->messageId->value)
                    && hash_equals((string) $row['message_checksum'], $message->checksum)
                    && json_decode((string) $row['payload'], true, 512, JSON_THROW_ON_ERROR) === $message->payload();

                return $same ? SecurityComplianceOutboxAppendResult::AlreadyApplied : SecurityComplianceOutboxAppendResult::DivergentMessage;
            });
        } catch (PDOException) {
            return SecurityComplianceOutboxAppendResult::DependencyUnavailable;
        }
    }

    public function eligible(DateTimeImmutable $availableAt, int $limit): array
    {
        if ($limit < 1 || $limit > 100) {
            throw new RuntimeException('SecurityCompliance outbox limit must be between 1 and 100.');
        }
        $query = $this->connection->prepare($this->selectSql()." WHERE s.technical_status IN ('pending','retry_scheduled') AND s.available_at<=CAST(:available_at AS timestamptz) AND s.attempts<:max_attempts ORDER BY s.available_at,j.created_at,j.message_id LIMIT :limit");
        $query->bindValue('available_at', $this->canonical($availableAt));
        $query->bindValue('max_attempts', self::MAX_ATTEMPTS, PDO::PARAM_INT);
        $query->bindValue('limit', $limit, PDO::PARAM_INT);
        $query->execute();
        /** @var list<array{message_id:string,event_id:string,message_type:string,delivery_status:string,observed_at:string,message_checksum:string,created_at:string,available_at:string,attempts:int|string,technical_status:string}> $rows */
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);

        return array_map($this->mapper->fromRow(...), $rows);
    }

    public function claim(SecurityComplianceOutboxMessageId $messageId, DateTimeImmutable $claimedAt): SecurityComplianceOutboxClaimResult
    {
        try {
            return $this->transactional(function () use ($messageId, $claimedAt): SecurityComplianceOutboxClaimResult {
                if (! $this->exists($messageId)) {
                    return SecurityComplianceOutboxClaimResult::Missing;
                }
                $row = $this->lockedRow($messageId);
                if ($row === false) {
                    return SecurityComplianceOutboxClaimResult::AlreadyClaimed;
                }
                try {
                    $message = $this->mapper->fromRow($row);
                } catch (Throwable) {
                    return SecurityComplianceOutboxClaimResult::Corrupted;
                }
                if ($message->status === SecurityComplianceOutboxMessageStatus::Completed) {
                    return SecurityComplianceOutboxClaimResult::AlreadyCompleted;
                }
                if ($message->status === SecurityComplianceOutboxMessageStatus::Claimed) {
                    return SecurityComplianceOutboxClaimResult::AlreadyClaimed;
                }
                if ($message->attempts >= self::MAX_ATTEMPTS) {
                    $this->exhaust($messageId);

                    return SecurityComplianceOutboxClaimResult::AttemptsExhausted;
                }
                if ($message->availableAt > $claimedAt) {
                    return SecurityComplianceOutboxClaimResult::AlreadyClaimed;
                }
                $update = $this->connection->prepare("UPDATE security_compliance.outbox_message_state SET technical_status='claimed',attempts=attempts+1,claimed_at=CAST(:claimed_at AS timestamptz) WHERE message_id=:message_id");
                $update->execute(['claimed_at' => $this->canonical($claimedAt), 'message_id' => $messageId->value]);

                return SecurityComplianceOutboxClaimResult::Claimed;
            });
        } catch (PDOException) {
            return SecurityComplianceOutboxClaimResult::DependencyUnavailable;
        }
    }

    public function retry(SecurityComplianceOutboxMessageId $messageId, DateTimeImmutable $availableAt): SecurityComplianceOutboxRetryResult
    {
        try {
            return $this->transactional(function () use ($messageId, $availableAt): SecurityComplianceOutboxRetryResult {
                $row = $this->lockedRow($messageId);
                if ($row === false) {
                    return SecurityComplianceOutboxRetryResult::Missing;
                }
                try {
                    $message = $this->mapper->fromRow($row);
                } catch (Throwable) {
                    return SecurityComplianceOutboxRetryResult::Corrupted;
                }
                if ($message->status === SecurityComplianceOutboxMessageStatus::Completed) {
                    return SecurityComplianceOutboxRetryResult::AlreadyCompleted;
                }
                if ($message->attempts >= self::MAX_ATTEMPTS) {
                    $this->exhaust($messageId);

                    return SecurityComplianceOutboxRetryResult::AttemptsExhausted;
                }
                $update = $this->connection->prepare("UPDATE security_compliance.outbox_message_state SET technical_status='retry_scheduled',available_at=CAST(:available_at AS timestamptz),claimed_at=NULL WHERE message_id=:message_id");
                $update->execute(['available_at' => $this->canonical($availableAt), 'message_id' => $messageId->value]);

                return SecurityComplianceOutboxRetryResult::RetryScheduled;
            });
        } catch (PDOException) {
            return SecurityComplianceOutboxRetryResult::DependencyUnavailable;
        }
    }

    /** @return array{message_id:string,event_id:string,message_type:string,delivery_status:string,observed_at:string,message_checksum:string,created_at:string,available_at:string,attempts:int|string,technical_status:string}|false */
    private function lockedRow(SecurityComplianceOutboxMessageId $messageId): array|false
    {
        $query = $this->connection->prepare($this->selectSql().' WHERE j.message_id=:message_id FOR UPDATE OF s SKIP LOCKED');
        $query->execute(['message_id' => $messageId->value]);

        /** @var array{message_id:string,event_id:string,message_type:string,delivery_status:string,observed_at:string,message_checksum:string,created_at:string,available_at:string,attempts:int|string,technical_status:string}|false */
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    private function exhaust(SecurityComplianceOutboxMessageId $messageId): void
    {
        $query = $this->connection->prepare("UPDATE security_compliance.outbox_message_state SET technical_status='attempts_exhausted',claimed_at=NULL WHERE message_id=:message_id");
        $query->execute(['message_id' => $messageId->value]);
    }

    private function exists(SecurityComplianceOutboxMessageId $messageId): bool
    {
        $query = $this->connection->prepare('SELECT 1 FROM security_compliance.outbox_message_state WHERE message_id=:message_id');
        $query->execute(['message_id' => $messageId->value]);

        return $query->fetchColumn() !== false;
    }

    private function selectSql(): string
    {
        return 'SELECT j.message_id,j.event_id,j.message_type,j.delivery_status,j.observed_at,j.message_checksum,j.created_at,s.available_at,s.attempts,s.technical_status FROM security_compliance.outbox_message_journal j JOIN security_compliance.outbox_message_state s ON s.message_id=j.message_id';
    }

    private function transactional(callable $operation): mixed
    {
        $owner = ! $this->connection->inTransaction();
        $owner ? $this->connection->beginTransaction() : $this->connection->exec('SAVEPOINT '.self::SAVEPOINT);
        try {
            $result = $operation();
            $owner ? $this->connection->commit() : $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);

            return $result;
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            } else {
                $this->connection->exec('ROLLBACK TO SAVEPOINT '.self::SAVEPOINT);
                $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);
            }
            throw $error;
        }
    }

    private function canonical(DateTimeImmutable $instant): string
    {
        return $instant->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}

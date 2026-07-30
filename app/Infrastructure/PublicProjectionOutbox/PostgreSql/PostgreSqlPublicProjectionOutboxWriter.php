<?php

namespace App\Infrastructure\PublicProjectionOutbox\PostgreSql;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryStatus;
use App\Application\PublicProjectionDelivery\PublicProjectionRoutedDeliveryMessageV1;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxRoutedWriterV1;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimOwnerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxQuarantineDecision;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryClassification;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryDecision;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxWriteResult;
use PDO;
use Throwable;

final readonly class PostgreSqlPublicProjectionOutboxWriter implements PublicProjectionOutboxRoutedWriterV1, PublicProjectionOutboxWriter
{
    public function __construct(private PDO $connection, private PostgreSqlPublicProjectionOutboxMapper $mapper) {}

    public function append(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId): PublicProjectionOutboxWriteResult
    {
        return $this->appendDelivery($message, $consumerId);
    }

    public function appendRouted(
        PublicProjectionRoutedDeliveryMessageV1 $delivery,
        PublicProjectionOutboxConsumerId $consumerId,
    ): PublicProjectionOutboxWriteResult {
        if (! $delivery->hasValidRoutingProof()) {
            return PublicProjectionOutboxWriteResult::DivergentMessage;
        }

        return $this->appendDelivery($delivery->deliveryMessage, $consumerId, $delivery);
    }

    private function appendDelivery(
        PublicProjectionDeliveryMessage $message,
        PublicProjectionOutboxConsumerId $consumerId,
        ?PublicProjectionRoutedDeliveryMessageV1 $routedDelivery = null,
    ): PublicProjectionOutboxWriteResult {
        $schema = PostgreSqlPublicProjectionOutboxSchema::for($message->sourceModule);

        return $this->transactional(function () use ($schema, $message, $consumerId, $routedDelivery): PublicProjectionOutboxWriteResult {
            $parameters = $this->mapper->messageParameters($message);
            if ($routedDelivery === null) {
                $statement = $this->connection->prepare("INSERT INTO {$schema}.public_projection_outbox_messages (message_id,idempotency_key,source_module,aggregate_type,aggregate_id,aggregate_version,event_index,event_type,payload_version,payload,payload_checksum,occurred_at,recorded_at,correlation_id,causation_id) VALUES (:message_id,:idempotency_key,:source_module,:aggregate_type,:aggregate_id,:aggregate_version,:event_index,:event_type,:payload_version,CAST(:payload AS jsonb),:payload_checksum,CAST(:occurred_at AS timestamptz),CAST(:recorded_at AS timestamptz),:correlation_id,:causation_id) ON CONFLICT (idempotency_key) DO NOTHING");
            } else {
                $parameters += $this->mapper->routingParameters($routedDelivery);
                $statement = $this->connection->prepare("INSERT INTO {$schema}.public_projection_outbox_messages (message_id,idempotency_key,source_module,aggregate_type,aggregate_id,aggregate_version,event_index,event_type,payload_version,payload,payload_checksum,occurred_at,recorded_at,correlation_id,causation_id,routing_destination,routing_version,routing_checksum) VALUES (:message_id,:idempotency_key,:source_module,:aggregate_type,:aggregate_id,:aggregate_version,:event_index,:event_type,:payload_version,CAST(:payload AS jsonb),:payload_checksum,CAST(:occurred_at AS timestamptz),CAST(:recorded_at AS timestamptz),:correlation_id,:causation_id,:routing_destination,:routing_version,:routing_checksum) ON CONFLICT (idempotency_key) DO NOTHING");
            }
            $statement->execute($parameters);
            if ($statement->rowCount() === 0) {
                $routingColumns = $routedDelivery === null ? '' : ',routing_destination,routing_version,routing_checksum';
                $check = $this->connection->prepare("SELECT message_id,payload_checksum{$routingColumns} FROM {$schema}.public_projection_outbox_messages WHERE idempotency_key=:key");
                $check->execute(['key' => $message->idempotencyKey->value]);
                $existing = $check->fetch(PDO::FETCH_ASSOC);
                if (! is_array($existing)
                    || $existing['message_id'] !== $message->messageId->value
                    || $existing['payload_checksum'] !== $message->payload->checksum()
                    || ($routedDelivery !== null && (
                        $existing['routing_destination'] !== $routedDelivery->destination->value
                        || (int) $existing['routing_version'] !== $routedDelivery->routingProof->routingVersion
                        || $existing['routing_checksum'] !== $routedDelivery->routingProof->checksum
                    ))) {
                    return PublicProjectionOutboxWriteResult::DivergentMessage;
                }
            }
            $delivery = $this->connection->prepare("INSERT INTO {$schema}.public_projection_outbox_deliveries (message_id,consumer_id,status,attempts,claim_state) VALUES (:message,:consumer,'pending',0,'unclaimed') ON CONFLICT (message_id,consumer_id) DO NOTHING");
            $delivery->execute(['message' => $message->messageId->value, 'consumer' => $consumerId->value]);

            return $statement->rowCount() === 0 && $delivery->rowCount() === 0 ? PublicProjectionOutboxWriteResult::AlreadyApplied : PublicProjectionOutboxWriteResult::Applied;
        });
    }

    public function markDelivered(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $ownerId): PublicProjectionOutboxWriteResult
    {
        return $this->claimedUpdate($message, $consumerId, $ownerId, "status='delivered',claim_state='released',claim_owner_id=NULL,claimed_at=NULL,claimed_until=NULL,delivered_at=clock_timestamp()");
    }

    public function scheduleRetry(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $ownerId, PublicProjectionOutboxRetryDecision $decision): PublicProjectionOutboxWriteResult
    {
        $status = match ($decision->classification) {
            PublicProjectionOutboxRetryClassification::SourceNotReady => PublicProjectionDeliveryStatus::BlockedBySourceReadiness,
            PublicProjectionOutboxRetryClassification::SequenceGap => PublicProjectionDeliveryStatus::BlockedBySequenceGap,
            default => PublicProjectionDeliveryStatus::RetryScheduled,
        };
        $schema = PostgreSqlPublicProjectionOutboxSchema::for($message->sourceModule);
        $statement = $this->connection->prepare("UPDATE {$schema}.public_projection_outbox_deliveries SET status=:status,claim_state='released',claim_owner_id=NULL,claimed_at=NULL,claimed_until=NULL,retry_classification=:classification,retry_delay_seconds=:delay,retry_allowed=:allowed,available_at=CASE WHEN :retry_status='retry_scheduled' THEN clock_timestamp() + (:interval_delay * interval '1 second') ELSE NULL END WHERE message_id=:message AND consumer_id=:consumer AND status='claimed' AND claim_owner_id=:owner");
        $delay = $status === PublicProjectionDeliveryStatus::RetryScheduled ? $decision->backoff->delaySeconds : null;
        $statement->execute(['status' => $status->value, 'classification' => $status === PublicProjectionDeliveryStatus::RetryScheduled ? $decision->classification->value : null, 'delay' => $delay, 'allowed' => $status === PublicProjectionDeliveryStatus::RetryScheduled ? $decision->retryAllowed : null, 'retry_status' => $status->value, 'interval_delay' => $delay, 'message' => $message->messageId->value, 'consumer' => $consumerId->value, 'owner' => $ownerId->value]);

        return $statement->rowCount() === 1 ? PublicProjectionOutboxWriteResult::Applied : $this->missingOrClaimMismatch($schema, $message, $consumerId);
    }

    public function quarantine(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, ?PublicProjectionOutboxClaimOwnerId $ownerId, PublicProjectionOutboxQuarantineDecision $decision): PublicProjectionOutboxWriteResult
    {
        $schema = PostgreSqlPublicProjectionOutboxSchema::for($message->sourceModule);
        $statement = $this->connection->prepare("UPDATE {$schema}.public_projection_outbox_deliveries SET status='quarantined',claim_state='abandoned',claim_owner_id=NULL,claimed_at=NULL,claimed_until=NULL,quarantine_reason=:reason,last_error_code=:error WHERE message_id=:message AND consumer_id=:consumer AND (status<>'claimed' OR claim_owner_id=:owner)");
        $statement->execute(['reason' => $decision->reason->value, 'error' => $decision->errorCode, 'message' => $message->messageId->value, 'consumer' => $consumerId->value, 'owner' => $ownerId?->value]);

        return $statement->rowCount() === 1 ? PublicProjectionOutboxWriteResult::Applied : $this->missingOrClaimMismatch($schema, $message, $consumerId);
    }

    public function releaseClaim(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $ownerId): PublicProjectionOutboxWriteResult
    {
        return $this->claimedUpdate($message, $consumerId, $ownerId, "status='pending',claim_state='released',claim_owner_id=NULL,claimed_at=NULL,claimed_until=NULL");
    }

    private function claimedUpdate(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumer, PublicProjectionOutboxClaimOwnerId $owner, string $set): PublicProjectionOutboxWriteResult
    {
        $schema = PostgreSqlPublicProjectionOutboxSchema::for($message->sourceModule);
        $statement = $this->connection->prepare("UPDATE {$schema}.public_projection_outbox_deliveries SET {$set} WHERE message_id=:message AND consumer_id=:consumer AND status='claimed' AND claim_owner_id=:owner");
        $statement->execute(['message' => $message->messageId->value, 'consumer' => $consumer->value, 'owner' => $owner->value]);

        return $statement->rowCount() === 1 ? PublicProjectionOutboxWriteResult::Applied : $this->missingOrClaimMismatch($schema, $message, $consumer);
    }

    private function missingOrClaimMismatch(string $schema, PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumer): PublicProjectionOutboxWriteResult
    {
        $statement = $this->connection->prepare("SELECT 1 FROM {$schema}.public_projection_outbox_deliveries WHERE message_id=:message AND consumer_id=:consumer");
        $statement->execute(['message' => $message->messageId->value, 'consumer' => $consumer->value]);

        return $statement->fetchColumn() === false ? PublicProjectionOutboxWriteResult::NotFound : PublicProjectionOutboxWriteResult::ClaimMismatch;
    }

    private function transactional(callable $operation): mixed
    {
        if ($this->connection->inTransaction()) {
            return $operation();
        }
        $this->connection->beginTransaction();
        try {
            $result = $operation();
            $this->connection->commit();

            return $result;
        } catch (Throwable $error) {
            $this->connection->rollBack();
            throw $error;
        }
    }
}

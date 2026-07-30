<?php

namespace App\Infrastructure\PublicProjectionOutbox\PostgreSql;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxClaimManager;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimResult;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxLease;
use DateTimeImmutable;
use PDO;

final readonly class PostgreSqlPublicProjectionOutboxClaimManager implements PublicProjectionOutboxClaimManager
{
    public function __construct(private PDO $connection) {}

    public function claim(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxLease $lease): PublicProjectionOutboxClaimResult
    {
        $schema = PostgreSqlPublicProjectionOutboxSchema::for($message->sourceModule);
        $statement = $this->connection->prepare("WITH candidate AS (SELECT message_id,consumer_id,claimed_until FROM {$schema}.public_projection_outbox_deliveries WHERE message_id=:message AND consumer_id=:consumer AND status IN ('pending','retry_scheduled','blocked_by_sequence_gap','blocked_by_source_readiness','claimed') AND (status<>'claimed' OR claimed_until<=:claimed_check) FOR UPDATE), updated AS (UPDATE {$schema}.public_projection_outbox_deliveries d SET status='claimed',claim_state='claimed',claim_owner_id=:owner,claimed_at=:claimed,claimed_until=:expires,attempts=d.attempts+1,retry_classification=NULL,retry_delay_seconds=NULL,retry_allowed=NULL,available_at=NULL FROM candidate c WHERE d.message_id=c.message_id AND d.consumer_id=c.consumer_id RETURNING c.claimed_until) SELECT CASE WHEN claimed_until IS NULL THEN 'claimed' ELSE 'lease_expired' END FROM updated");
        $claimed = $lease->claimedAt->format('Y-m-d H:i:s.uP');
        $statement->execute(['owner' => $lease->ownerId->value, 'claimed' => $claimed, 'expires' => $lease->expiresAt->format('Y-m-d H:i:s.uP'), 'message' => $message->messageId->value, 'consumer' => $consumerId->value, 'claimed_check' => $claimed]);
        $result = $statement->fetchColumn();
        if (is_string($result)) {
            return PublicProjectionOutboxClaimResult::from($result);
        }

        $status = $this->status($schema, $message, $consumerId);

        return $status === null || in_array($status, ['delivered', 'quarantined'], true) ? PublicProjectionOutboxClaimResult::NothingToClaim : PublicProjectionOutboxClaimResult::AlreadyClaimed;
    }

    public function expire(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, DateTimeImmutable $at): PublicProjectionOutboxClaimResult
    {
        $schema = PostgreSqlPublicProjectionOutboxSchema::for($message->sourceModule);
        $statement = $this->connection->prepare("UPDATE {$schema}.public_projection_outbox_deliveries SET status='pending',claim_state='expired',claim_owner_id=NULL,claimed_at=NULL,claimed_until=NULL WHERE message_id=:message AND consumer_id=:consumer AND status='claimed' AND claimed_until<=:at");
        $statement->execute(['message' => $message->messageId->value, 'consumer' => $consumerId->value, 'at' => $at->format('Y-m-d H:i:s.uP')]);

        return $statement->rowCount() === 1 ? PublicProjectionOutboxClaimResult::LeaseExpired : PublicProjectionOutboxClaimResult::AlreadyClaimed;
    }

    public function abandon(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxLease $lease): PublicProjectionOutboxClaimResult
    {
        $schema = PostgreSqlPublicProjectionOutboxSchema::for($message->sourceModule);
        $statement = $this->connection->prepare("UPDATE {$schema}.public_projection_outbox_deliveries SET status='pending',claim_state='abandoned',claim_owner_id=NULL,claimed_at=NULL,claimed_until=NULL WHERE message_id=:message AND consumer_id=:consumer AND status='claimed' AND claim_owner_id=:owner AND claimed_until=:expires");
        $statement->execute(['message' => $message->messageId->value, 'consumer' => $consumerId->value, 'owner' => $lease->ownerId->value, 'expires' => $lease->expiresAt->format('Y-m-d H:i:s.uP')]);

        return $statement->rowCount() === 1 ? PublicProjectionOutboxClaimResult::Abandoned : PublicProjectionOutboxClaimResult::AlreadyClaimed;
    }

    private function status(string $schema, PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumer): ?string
    {
        $statement = $this->connection->prepare("SELECT status FROM {$schema}.public_projection_outbox_deliveries WHERE message_id=:message AND consumer_id=:consumer");
        $statement->execute(['message' => $message->messageId->value, 'consumer' => $consumer->value]);
        $status = $statement->fetchColumn();

        return is_string($status) ? $status : null;
    }
}

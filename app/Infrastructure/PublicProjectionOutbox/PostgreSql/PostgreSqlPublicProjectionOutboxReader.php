<?php

namespace App\Infrastructure\PublicProjectionOutbox\PostgreSql;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxReader;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRecord;
use PDO;

final readonly class PostgreSqlPublicProjectionOutboxReader implements PublicProjectionOutboxReader
{
    public function __construct(private PDO $connection, private PostgreSqlPublicProjectionOutboxMapper $mapper) {}

    public function findClaimable(PublicProjectionOutboxConsumerId $consumerId, int $limit): array
    {
        return array_slice($this->queryAll($consumerId, "(d.status IN ('pending','retry_scheduled') AND (d.available_at IS NULL OR d.available_at<=clock_timestamp())) OR (d.status='claimed' AND d.claimed_until<=clock_timestamp())"), 0, $limit);
    }

    public function findBlocked(PublicProjectionOutboxConsumerId $consumerId): array
    {
        return $this->queryAll($consumerId, "d.status IN ('blocked_by_sequence_gap','blocked_by_source_readiness')");
    }

    public function findRetryable(PublicProjectionOutboxConsumerId $consumerId): array
    {
        return $this->queryAll($consumerId, "d.status='retry_scheduled'");
    }

    public function findQuarantined(PublicProjectionOutboxConsumerId $consumerId): array
    {
        return $this->queryAll($consumerId, "d.status='quarantined'");
    }

    public function findByAggregate(PublicProjectionOutboxConsumerId $consumerId, PublicProjectionDeliveryAggregateId $aggregateId): array
    {
        return $this->queryAll($consumerId, 'm.aggregate_id=:aggregate', ['aggregate' => $aggregateId->value]);
    }

    /** @param array<string, string> $extra
     * @return list<PublicProjectionOutboxRecord>
     */
    private function queryAll(PublicProjectionOutboxConsumerId $consumer, string $predicate, array $extra = []): array
    {
        $records = [];
        foreach (PostgreSqlPublicProjectionOutboxSchema::all() as $schema) {
            $statement = $this->connection->prepare("SELECT m.*,d.* FROM {$schema}.public_projection_outbox_messages m JOIN {$schema}.public_projection_outbox_deliveries d USING(message_id) WHERE m.source_module=:owner_module AND d.consumer_id=:consumer AND {$predicate}");
            $statement->execute(['owner_module' => PostgreSqlPublicProjectionOutboxSchema::moduleFor($schema)->value, 'consumer' => $consumer->value] + $extra);
            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $records[] = $this->mapper->toRecord($row);
            }
        }
        usort($records, static fn (PublicProjectionOutboxRecord $left, PublicProjectionOutboxRecord $right): int => [$left->message->sourceModule->value, $left->message->aggregateType->value, $left->message->aggregateId->value, $left->message->order->aggregateVersion, $left->message->order->eventIndex->value] <=> [$right->message->sourceModule->value, $right->message->aggregateType->value, $right->message->aggregateId->value, $right->message->order->aggregateVersion, $right->message->order->eventIndex->value]);

        return $records;
    }
}

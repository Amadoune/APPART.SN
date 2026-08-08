<?php

namespace Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionDelivery\SearchQueryResolutionDeliveryV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOutbox\SearchQueryResolutionOutboxPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOutbox\SearchQueryResolutionOutboxReader;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOutbox\SearchQueryResolutionOutboxResult;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOutbox\SearchQueryResolutionOutboxStatus;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOutbox\SearchQueryResolutionOutboxWriter;
use PDO;
use RuntimeException;

final readonly class SearchQueryResolutionOutboxRepository implements SearchQueryResolutionOutboxReader, SearchQueryResolutionOutboxWriter
{
    public function __construct(private PDO $connection, private SearchQueryResolutionOutboxPolicy $policy) {}

    public function append(SearchQueryResolutionDeliveryV1 $delivery): SearchQueryResolutionOutboxResult
    {
        $entry = $this->policy->prepare($delivery);
        $payload = json_encode(['status' => $entry->status->value, 'observedAt' => $entry->observedAt], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $statement = $this->connection->prepare(
            'INSERT INTO search_discovery.search_query_resolution_outbox (outbox_id, status, observed_at, payload) VALUES (:id, :status, :observed_at, CAST(:payload AS jsonb)) ON CONFLICT (outbox_id) DO NOTHING',
        );
        $statement->execute(['id' => $entry->outboxId, 'status' => $entry->status->value, 'observed_at' => $entry->observedAt, 'payload' => $payload]);

        if ($statement->rowCount() === 1) {
            return $entry;
        }

        $existing = $this->connection->prepare('SELECT payload::text FROM search_discovery.search_query_resolution_outbox WHERE outbox_id = :id');
        $existing->execute(['id' => $entry->outboxId]);
        $stored = $existing->fetchColumn();
        if (! is_string($stored) || json_decode($stored, true, 512, JSON_THROW_ON_ERROR) !== json_decode($payload, true, 512, JSON_THROW_ON_ERROR)) {
            throw new RuntimeException('Divergent outbox entry.');
        }

        return new SearchQueryResolutionOutboxResult($entry->outboxId, $entry->status, $entry->observedAt, true);
    }

    public function pending(int $limit): array
    {
        if ($limit < 1 || $limit > 100) {
            throw new RuntimeException('Outbox pending limit must be between 1 and 100.');
        }
        $statement = $this->connection->prepare('SELECT outbox_id, status, observed_at FROM search_discovery.search_query_resolution_outbox WHERE delivered_at IS NULL ORDER BY created_at, outbox_id LIMIT :limit');
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return array_map(
            static fn (array $row): SearchQueryResolutionOutboxResult => new SearchQueryResolutionOutboxResult(
                (string) $row['outbox_id'],
                SearchQueryResolutionOutboxStatus::from((string) $row['status']),
                (string) $row['observed_at'],
                true,
            ),
            $statement->fetchAll(PDO::FETCH_ASSOC),
        );
    }
}

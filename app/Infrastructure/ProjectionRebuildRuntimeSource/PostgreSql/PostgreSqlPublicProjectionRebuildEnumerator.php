<?php

namespace App\Infrastructure\ProjectionRebuildRuntimeSource\PostgreSql;

use App\Application\PublicProjectionRebuild\Contract\PublicProjectionRebuildEnumerator;
use App\Application\PublicProjectionRebuild\PublicProjectionRebuildPage;
use App\Application\PublicProjectionRebuild\PublicProjectionRebuildScope;
use App\Application\PublicProjectionRebuild\PublicProjectionRebuildScopeType;
use App\Infrastructure\ProjectionRebuildRuntimeSource\RebuildCheckpointCodec;
use InvalidArgumentException;
use PDO;

final readonly class PostgreSqlPublicProjectionRebuildEnumerator implements PublicProjectionRebuildEnumerator
{
    public function __construct(private PDO $connection, private RebuildCheckpointCodec $checkpoints) {}

    public function page(PublicProjectionRebuildScope $scope, ?string $checkpoint, int $limit): PublicProjectionRebuildPage
    {
        if ($limit < 1) {
            throw new InvalidArgumentException('The rebuild enumeration limit must be positive.');
        }
        $after = $checkpoint === null ? null : $this->checkpoints->decode($scope, $checkpoint);

        $ids = match ($scope->type) {
            PublicProjectionRebuildScopeType::Full => $this->databasePage(null, null, $after, $limit),
            PublicProjectionRebuildScopeType::Range => $this->databasePage($scope->from, $scope->to, $after, $limit),
            PublicProjectionRebuildScopeType::Listings => $this->listingPage($scope->listingIds, $after, $limit),
        };

        $hasMore = count($ids) > $limit;
        if ($hasMore) {
            array_pop($ids);
        }
        $next = $hasMore && $ids !== [] ? $this->checkpoints->encode($scope, $ids[array_key_last($ids)]) : null;

        return new PublicProjectionRebuildPage($ids, $next);
    }

    /** @return list<string> */
    private function databasePage(?string $from, ?string $to, ?string $after, int $limit): array
    {
        foreach ([$from, $to, $after] as $identity) {
            if ($identity !== null && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $identity) !== 1) {
                throw new InvalidArgumentException('A rebuild database bound must be a Listing UUID.');
            }
        }
        $where = [];
        $parameters = [];
        if ($from !== null) {
            $where[] = 'id >= CAST(:from_id AS uuid)';
            $parameters['from_id'] = $from;
        }
        if ($to !== null) {
            $where[] = 'id <= CAST(:to_id AS uuid)';
            $parameters['to_id'] = $to;
        }
        if ($after !== null) {
            $where[] = 'id > CAST(:after_id AS uuid)';
            $parameters['after_id'] = $after;
        }
        $sql = 'SELECT id::text FROM listing_lifecycle.listings'.($where === [] ? '' : ' WHERE '.implode(' AND ', $where)).' ORDER BY id LIMIT '.($limit + 1);
        $statement = $this->connection->prepare($sql);
        $statement->execute($parameters);

        return array_values(array_map('strval', $statement->fetchAll(PDO::FETCH_COLUMN)));
    }

    /**
     * @param  list<string>  $listingIds
     * @return list<string>
     */
    private function listingPage(array $listingIds, ?string $after, int $limit): array
    {
        sort($listingIds, SORT_STRING);
        if ($after !== null) {
            $listingIds = array_values(array_filter($listingIds, static fn (string $id): bool => strcmp($id, $after) > 0));
        }

        return array_slice($listingIds, 0, $limit + 1);
    }
}

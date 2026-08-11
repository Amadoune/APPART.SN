<?php

namespace App\Infrastructure\PublicProjectionStore\PostgreSql;

use App\Application\PublicSearchResults\Contract\PublicSearchResultsReaderV1;
use App\Application\PublicSearchResults\PublicSearchListingSummary;
use App\Application\PublicSearchResults\PublicSearchResultsQuery;
use App\Application\PublicSearchResults\PublicSearchResultsResult;
use PDO;
use PDOException;
use RuntimeException;

final readonly class PostgreSqlPublicSearchResultsReader implements PublicSearchResultsReaderV1
{
    public function __construct(
        private PDO $connection,
        private PostgreSqlPublicListingProjectionMapper $mapper,
    ) {}

    public function read(PublicSearchResultsQuery $query): PublicSearchResultsResult
    {
        try {
            $rows = $this->rows($query);
            $hasNextPage = count($rows) > $query->limit;
            $page = array_slice($rows, 0, $query->limit);

            if ($page === []) {
                return PublicSearchResultsResult::empty();
            }

            $items = array_map(function (array $row): PublicSearchListingSummary {
                $model = $this->mapper->toRecord($row)->readModel;
                if ($model === null) {
                    throw new RuntimeException('Current public projection has no read model.');
                }

                return new PublicSearchListingSummary(
                    canonicalPath: (string) $row['canonical_path'],
                    listingId: $model->listingId,
                    headline: $model->headline,
                    propertyType: $model->propertyType,
                    primaryImageUrl: $model->publicMediaUrl,
                    transaction: $model->transactionKind,
                    city: $model->city,
                    surfaceSquareMeters: $model->surfaceSquareMeters,
                    roomCount: $model->roomCount,
                );
            }, $page);

            $nextCursor = $hasNextPage ? $items[array_key_last($items)]->canonicalPath : null;

            return PublicSearchResultsResult::available($items, $nextCursor);
        } catch (PDOException) {
            return PublicSearchResultsResult::dependencyUnavailable();
        } catch (RuntimeException) {
            return PublicSearchResultsResult::corrupted();
        }
    }

    /** @return list<array<string, mixed>> */
    private function rows(PublicSearchResultsQuery $query): array
    {
        $clauses = [];
        if ($query->afterCanonicalPath !== null) {
            $clauses[] = 'p.canonical_path > :after';
        }
        if ($query->transaction !== null) {
            $clauses[] = 'p.transaction_kind = :transaction';
        }
        if ($query->city !== null) {
            $clauses[] = 'lower(p.city) = lower(:city)';
        }
        if ($query->propertyType !== null) {
            $clauses[] = 'lower(p.property_type) = lower(:property_type)';
        }
        $filterClause = $clauses === [] ? '' : ' AND '.implode(' AND ', $clauses);
        $statement = $this->connection->prepare(
            "SELECT p.* FROM public_projection.listing_projections p
             JOIN public_projection.generations g USING(generation_id)
             WHERE g.state='active' AND p.state='current'{$filterClause}
             ORDER BY p.canonical_path ASC
             LIMIT :limit",
        );
        if ($query->afterCanonicalPath !== null) {
            $statement->bindValue('after', $query->afterCanonicalPath);
        }
        foreach (['transaction' => $query->transaction, 'city' => $query->city, 'property_type' => $query->propertyType] as $parameter => $value) {
            if ($value !== null) {
                $statement->bindValue($parameter, $value);
            }
        }
        $statement->bindValue('limit', $query->limit + 1, PDO::PARAM_INT);
        $statement->execute();

        /** @var list<array<string, mixed>> $rows */
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }
}

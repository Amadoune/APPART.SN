<?php

namespace App\Infrastructure\PublicProjectionStore\PostgreSql;

use App\Application\Contract\PublicListingQuery;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\ReadModels\PublicListingReadModel;
use PDO;

final readonly class PostgreSqlPublicListingProjectionReader implements PublicListingQuery
{
    public function __construct(private PDO $connection, private PostgreSqlPublicListingProjectionMapper $mapper) {}

    public function findByCanonicalPath(string $canonicalPath): ?PublicListingReadModel
    {
        $statement = $this->connection->prepare("SELECT p.* FROM public_projection.listing_projections p JOIN public_projection.generations g USING(generation_id) WHERE g.state='active' AND p.state='current' AND p.canonical_path=:canonical");
        $statement->execute(['canonical' => $canonicalPath]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->mapper->toRecord($row)->readModel : null;
    }

    public function snapshot(PublicProjectionGenerationId $generation, string $canonicalPath, bool $forUpdate = false): ?PublicListingProjectionRecord
    {
        $lock = $forUpdate ? ' FOR UPDATE' : '';
        $statement = $this->connection->prepare("SELECT * FROM public_projection.listing_projections WHERE generation_id=:generation AND canonical_path=:canonical{$lock}");
        $statement->execute(['generation' => $generation->value, 'canonical' => $canonicalPath]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->mapper->toRecord($row) : null;
    }

    public function forListing(PublicProjectionGenerationId $generation, string $listingId, bool $forUpdate = false): ?PublicListingProjectionRecord
    {
        $lock = $forUpdate ? ' FOR UPDATE' : '';
        $statement = $this->connection->prepare("SELECT * FROM public_projection.listing_projections WHERE generation_id=:generation AND listing_id=:listing AND state<>'historical'{$lock}");
        $statement->execute(['generation' => $generation->value, 'listing' => $listingId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->mapper->toRecord($row) : null;
    }
}

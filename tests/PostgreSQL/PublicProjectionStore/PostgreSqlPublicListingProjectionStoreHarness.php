<?php

namespace Tests\PostgreSQL\PublicProjectionStore;

use App\Application\Contract\PublicListingQuery;
use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionMapper;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionReader;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionStore;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionWriter;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\PublicProjectionStore\PublicListingProjectionStoreHarness;

final class PostgreSqlPublicListingProjectionStoreHarness implements PublicListingProjectionStoreHarness
{
    private PublicProjectionGenerationId $active;

    private PublicProjectionGenerationId $candidate;

    private PostgreSqlPublicListingProjectionReader $reader;

    private PostgreSqlPublicListingProjectionStore $store;

    public function __construct(private readonly PDO $connection)
    {
        PostgreSqlTestEnvironment::migrate($connection);
        PostgreSqlTestEnvironment::reset($connection);
        $this->active = PublicProjectionGenerationId::fromString('96000000-0000-4000-8000-000000000001');
        $this->candidate = PublicProjectionGenerationId::fromString('96000000-0000-4000-8000-000000000002');
        $statement = $connection->prepare('INSERT INTO public_projection.generations (generation_id,state) VALUES (:active,\'active\'),(:candidate,\'candidate\')');
        $statement->execute(['active' => $this->active->value, 'candidate' => $this->candidate->value]);
        $mapper = new PostgreSqlPublicListingProjectionMapper;
        $this->reader = new PostgreSqlPublicListingProjectionReader($connection, $mapper);
        $writer = new PostgreSqlPublicListingProjectionWriter($connection, $mapper, $this->reader);
        $this->store = new PostgreSqlPublicListingProjectionStore($writer, $this->reader);
    }

    public function writer(): PublicListingProjectionWriter
    {
        return $this->store;
    }

    public function query(): PublicListingQuery
    {
        return $this->store;
    }

    public function activeGenerationId(): PublicProjectionGenerationId
    {
        return $this->active;
    }

    public function candidateGenerationId(): PublicProjectionGenerationId
    {
        return $this->candidate;
    }

    public function snapshot(PublicProjectionGenerationId $generationId, string $canonicalPath): ?PublicListingProjectionRecord
    {
        return $this->reader->snapshot($generationId, $canonicalPath);
    }
}

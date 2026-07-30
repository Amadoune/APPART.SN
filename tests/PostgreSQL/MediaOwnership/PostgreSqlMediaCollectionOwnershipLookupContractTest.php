<?php

namespace Tests\PostgreSQL\MediaOwnership;

use Appart\Modules\Media\Application\Contract\MediaCollectionOwnershipLookup;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaCollectionOwnershipLookup;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\MediaOwnership\MediaCollectionOwnershipLookupContract;

final class PostgreSqlMediaCollectionOwnershipLookupContractTest extends MediaCollectionOwnershipLookupContract
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    protected function lookup(): MediaCollectionOwnershipLookup
    {
        return new PostgreSqlMediaCollectionOwnershipLookup($this->connection);
    }

    protected function own(PropertyId $propertyId, MediaCollectionId $collectionId): void
    {
        $statement = $this->connection->prepare("INSERT INTO media.media_collections(id,property_id,last_changed_at,last_changed_at_offset,version) VALUES(:id,:property,'2026-07-19T00:00:00+00:00',0,0)");
        $statement->execute(['id' => $collectionId->value, 'property' => $propertyId->value]);
    }
}

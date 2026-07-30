<?php

namespace Appart\Modules\Media\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Media\Application\Contract\MediaCollectionOwnershipLookup;
use Appart\Modules\Media\Application\Ownership\MediaOwnershipResult;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;
use PDO;

final readonly class PostgreSqlMediaCollectionOwnershipLookup implements MediaCollectionOwnershipLookup
{
    public function __construct(private PDO $connection) {}

    public function resolve(PropertyId $propertyId): MediaOwnershipResult
    {
        $statement = $this->connection->prepare('SELECT id FROM media.media_collections WHERE property_id=:property_id ORDER BY id LIMIT 2');
        $statement->execute(['property_id' => $propertyId->value]);
        $ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        if ($ids === []) {
            return MediaOwnershipResult::missing($propertyId);
        }
        if (count($ids) === 1) {
            return MediaOwnershipResult::found($propertyId, MediaCollectionId::fromString((string) $ids[0]));
        }

        return MediaOwnershipResult::ambiguous($propertyId);
    }
}

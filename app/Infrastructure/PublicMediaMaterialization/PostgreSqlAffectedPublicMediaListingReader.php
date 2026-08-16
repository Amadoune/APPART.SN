<?php

namespace App\Infrastructure\PublicMediaMaterialization;

use App\Application\PublicMediaMaterialization\Contract\AffectedPublicMediaListingReaderV1;
use PDO;
use Throwable;

final readonly class PostgreSqlAffectedPublicMediaListingReader implements AffectedPublicMediaListingReaderV1
{
    public function __construct(private PDO $connection) {}

    public function listingIdForMedia(string $mediaId): ?string
    {
        try {
            $statement = $this->connection->prepare("SELECT l.id::text FROM media.media_items i JOIN media.media_collections c ON c.id=i.collection_id JOIN listing_lifecycle.listings l ON l.property_id=c.property_id WHERE i.media_id=CAST(:media_id AS uuid) AND l.status='published' ORDER BY l.id LIMIT 1");
            $statement->execute(['media_id' => $mediaId]);
            $value = $statement->fetchColumn();

            return is_string($value) ? $value : null;
        } catch (Throwable) {
            return null;
        }
    }
}

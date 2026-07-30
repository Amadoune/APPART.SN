<?php

namespace Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ContentSeo\Application\Contract\ContentSeoSourceSnapshotReader;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSnapshotReadResult;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\ContentSeoSourceSnapshotMapper;
use PDO;
use Throwable;

final readonly class PostgreSqlContentSeoSourceSnapshotReader implements ContentSeoSourceSnapshotReader
{
    public function __construct(private PDO $connection, private ContentSeoSourceSnapshotMapper $mapper) {}

    public function readByListing(ListingId $listingId): ContentSeoSnapshotReadResult
    {
        $statement = $this->connection->prepare('SELECT snapshot_id,listing_id,version,payload::text AS payload,payload_checksum FROM content_seo.public_source_snapshots WHERE listing_id=:listing_id');
        $statement->execute(['listing_id' => $listingId->value]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return ContentSeoSnapshotReadResult::missing($listingId);
        }
        try {
            return ContentSeoSnapshotReadResult::found($listingId, $this->mapper->toSnapshot($row));
        } catch (Throwable) {
            return ContentSeoSnapshotReadResult::corrupted($listingId);
        }
    }
}

<?php

namespace App\Infrastructure\PublicMediaMaterialization;

use App\Application\PublicMediaMaterialization\Contract\PublicMediaOwnerSourceReaderV2;
use App\Application\PublicMediaMaterialization\PublicMediaOwnerItemV2;
use App\Application\PublicMediaMaterialization\PublicMediaOwnerSourceResult;
use App\Application\PublicMediaMaterialization\PublicMediaOwnerSourceStatus;
use App\Application\PublicMediaMaterialization\PublicMediaOwnerSourceV2;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use PDO;
use Throwable;

final readonly class PostgreSqlPublicMediaOwnerSourceReaderV2 implements PublicMediaOwnerSourceReaderV2
{
    public function __construct(private PDO $connection) {}

    public function read(ListingId $listingId): PublicMediaOwnerSourceResult
    {
        try {
            $statement = $this->connection->prepare(<<<'SQL'
                SELECT l.id::text AS listing_id,l.status AS listing_state,l.version AS publication_version,
                       c.id::text AS collection_id,c.version AS collection_version,
                       i.media_id::text,i.media_order,i.is_primary,i.status AS media_status,
                       a.state AS asset_state,a.version AS asset_version,a.payload::text AS asset_payload,
                       att.result AS attachment_result,att.aggregate_version AS attachment_version,att.checksum AS attachment_checksum
                FROM listing_lifecycle.listings l
                LEFT JOIN media.media_collections c ON c.property_id=l.property_id
                LEFT JOIN media.media_items i ON i.collection_id=c.id
                LEFT JOIN media_ingestion.assets a ON a.aggregate_id=i.media_id
                LEFT JOIN LATERAL (
                    SELECT result,aggregate_version,checksum
                    FROM media.media_attachment_intents
                    WHERE media_id=i.media_id AND collection_id=i.collection_id AND property_id=l.property_id
                    ORDER BY created_at DESC,intent_id DESC LIMIT 1
                ) att ON true
                WHERE l.id=CAST(:listing_id AS uuid)
                ORDER BY i.media_order ASC,i.media_id ASC
                SQL);
            $statement->execute(['listing_id' => $listingId->value]);
            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($rows === []) {
                return new PublicMediaOwnerSourceResult(PublicMediaOwnerSourceStatus::Missing);
            }
            $first = $rows[0];
            if (($first['listing_state'] ?? null) !== 'published' || ! is_numeric($first['publication_version'] ?? null)) {
                return new PublicMediaOwnerSourceResult(PublicMediaOwnerSourceStatus::NotReady);
            }
            if (! is_string($first['collection_id'] ?? null) || ! is_numeric($first['collection_version'] ?? null)) {
                return new PublicMediaOwnerSourceResult(PublicMediaOwnerSourceStatus::NotReady);
            }
            $items = [];
            foreach ($rows as $row) {
                if (($row['media_status'] ?? null) !== 'active') {
                    continue;
                }
                $payload = json_decode((string) ($row['asset_payload'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
                if (($row['asset_state'] ?? null) !== 'ready'
                    || ($row['attachment_result'] ?? null) !== 'applied'
                    || ! is_array($payload)
                    || ! is_string($payload['contentChecksum'] ?? null)
                    || ! is_numeric($row['asset_version'] ?? null)
                    || ! is_numeric($row['attachment_version'] ?? null)
                    || ! is_string($row['attachment_checksum'] ?? null)
                    || ! is_numeric($row['media_order'] ?? null)
                    || ! is_bool($row['is_primary'] ?? null)) {
                    return new PublicMediaOwnerSourceResult(PublicMediaOwnerSourceStatus::NotReady);
                }
                $items[] = new PublicMediaOwnerItemV2(
                    (string) $row['media_id'],
                    (int) $row['media_order'],
                    $row['is_primary'],
                    (int) $row['asset_version'],
                    (string) $row['asset_state'],
                    $payload['contentChecksum'],
                    (int) $row['attachment_version'],
                    $row['attachment_checksum'],
                );
            }
            if ($items === [] || count(array_filter($items, static fn (PublicMediaOwnerItemV2 $item): bool => $item->primary)) !== 1) {
                return new PublicMediaOwnerSourceResult(PublicMediaOwnerSourceStatus::NotReady);
            }
            foreach ($items as $index => $item) {
                if ($item->order !== $index + 1) {
                    return new PublicMediaOwnerSourceResult(PublicMediaOwnerSourceStatus::Corrupted);
                }
            }

            return new PublicMediaOwnerSourceResult(PublicMediaOwnerSourceStatus::Ready, new PublicMediaOwnerSourceV2(
                (string) $first['listing_id'],
                (int) $first['publication_version'],
                (string) $first['listing_state'],
                $first['collection_id'],
                (int) $first['collection_version'],
                $items,
            ));
        } catch (Throwable) {
            return new PublicMediaOwnerSourceResult(PublicMediaOwnerSourceStatus::DependencyUnavailable);
        }
    }
}

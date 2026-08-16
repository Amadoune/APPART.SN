<?php

namespace App\Infrastructure\PublicMediaBinaryDelivery;

use App\Application\PublicMediaBinaryDelivery\Contract\PublicMediaBinaryOwnerSourceReader;
use App\Application\PublicMediaBinaryDelivery\PublicMediaBinarySource;
use App\Application\PublicMediaBinaryDelivery\PublicMediaBinarySourceResult;
use App\Application\PublicMediaBinaryDelivery\PublicMediaBinarySourceStatus;
use PDO;
use Throwable;

final readonly class PostgreSqlPublicMediaBinaryOwnerSourceReader implements PublicMediaBinaryOwnerSourceReader
{
    public function __construct(private PDO $connection) {}

    public function read(string $mediaId): PublicMediaBinarySourceResult
    {
        try {
            $statement = $this->connection->prepare(<<<'SQL'
                SELECT
                    i.media_id::text,
                    i.status AS media_status,
                    a.state AS asset_state,
                    a.version AS asset_version,
                    a.payload::text AS asset_payload,
                    attachment.result AS attachment_result,
                    EXISTS (
                        SELECT 1
                        FROM listing_lifecycle.listings l
                        JOIN listing_lifecycle.authoring_public_fact_handoffs h ON h.listing_id = l.id
                        WHERE l.property_id = c.property_id
                          AND l.status = 'published'
                          AND h.status = 'published'
                    ) AS listing_published
                FROM media.media_items i
                JOIN media.media_collections c ON c.id = i.collection_id
                LEFT JOIN media_ingestion.assets a ON a.aggregate_id = i.media_id
                LEFT JOIN LATERAL (
                    SELECT result
                    FROM media.media_attachment_intents
                    WHERE media_id = i.media_id
                      AND collection_id = i.collection_id
                      AND property_id = c.property_id
                    ORDER BY created_at DESC, intent_id DESC
                    LIMIT 1
                ) attachment ON true
                WHERE i.media_id = CAST(:media_id AS uuid)
                SQL);
            $statement->execute(['media_id' => $mediaId]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                return new PublicMediaBinarySourceResult(PublicMediaBinarySourceStatus::Missing);
            }
            $payload = json_decode((string) ($row['asset_payload'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($payload)
                || ! is_string($payload['ownerId'] ?? null)
                || ! is_string($payload['contentType'] ?? null)
                || ! is_string($payload['contentChecksum'] ?? null)
                || ! is_int($payload['bytes'] ?? null)
                || ! is_string($row['asset_state'] ?? null)
                || ! is_numeric($row['asset_version'] ?? null)
                || ! is_string($row['attachment_result'] ?? null)) {
                return new PublicMediaBinarySourceResult(PublicMediaBinarySourceStatus::Corrupted);
            }

            return new PublicMediaBinarySourceResult(
                PublicMediaBinarySourceStatus::Found,
                new PublicMediaBinarySource(
                    (string) $row['media_id'],
                    strtolower($payload['ownerId']),
                    (string) $row['media_status'],
                    $row['asset_state'],
                    (int) $row['asset_version'],
                    $payload['contentType'],
                    $payload['contentChecksum'],
                    $payload['bytes'],
                    $row['attachment_result'],
                    filter_var($row['listing_published'], FILTER_VALIDATE_BOOL),
                ),
            );
        } catch (Throwable) {
            return new PublicMediaBinarySourceResult(PublicMediaBinarySourceStatus::DependencyUnavailable);
        }
    }
}

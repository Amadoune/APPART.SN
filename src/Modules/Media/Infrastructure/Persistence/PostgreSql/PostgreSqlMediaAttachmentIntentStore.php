<?php

namespace Appart\Modules\Media\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Media\Application\Attachment\Contract\MediaAttachmentIntentStore;
use Appart\Modules\Media\Application\Attachment\MediaAttachmentIntent;
use PDO;
use RuntimeException;

final readonly class PostgreSqlMediaAttachmentIntentStore implements MediaAttachmentIntentStore
{
    public function __construct(private PDO $connection) {}

    public function find(string $intentId): ?MediaAttachmentIntent
    {
        $statement = $this->connection->prepare("SELECT checksum,collection_id,property_id,media_id,result,aggregate_version FROM media.media_attachment_intents WHERE operation='AttachReadyMediaAssetV1' AND intent_id=:intent_id");
        $statement->execute(['intent_id' => $intentId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        return new MediaAttachmentIntent($intentId, (string) $row['checksum'], (string) $row['collection_id'], (string) $row['property_id'], (string) $row['media_id'], $row['result'] === 'applied' ? (int) $row['aggregate_version'] : null);
    }

    public function reserve(MediaAttachmentIntent $intent): bool
    {
        $statement = $this->connection->prepare("INSERT INTO media.media_attachment_intents(operation,intent_id,checksum,collection_id,property_id,media_id,result,aggregate_version) VALUES('AttachReadyMediaAssetV1',:intent_id,:checksum,:collection_id,:property_id,:media_id,'pending',NULL) ON CONFLICT(operation,intent_id) DO NOTHING");
        $statement->execute(['intent_id' => $intent->intentId, 'checksum' => $intent->checksum, 'collection_id' => $intent->collectionId, 'property_id' => $intent->propertyId, 'media_id' => $intent->mediaId]);

        return $statement->rowCount() === 1;
    }

    public function markApplied(string $intentId, int $aggregateVersion): void
    {
        $statement = $this->connection->prepare("UPDATE media.media_attachment_intents SET result='applied',aggregate_version=:version WHERE operation='AttachReadyMediaAssetV1' AND intent_id=:intent_id AND result='pending'");
        $statement->execute(['intent_id' => $intentId, 'version' => $aggregateVersion]);
        if ($statement->rowCount() !== 1) {
            throw new RuntimeException('Media attachment intent cannot be completed.');
        }
    }
}

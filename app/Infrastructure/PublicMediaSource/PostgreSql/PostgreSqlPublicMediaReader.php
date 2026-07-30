<?php

namespace App\Infrastructure\PublicMediaSource\PostgreSql;

use App\Application\PublicMediaRevision\Contract\PublicMediaRevisionReader;
use App\Application\PublicMediaRevision\PublicMediaRevision;
use App\Application\PublicMediaSource\Contract\PublicMediaDecisionReader;
use App\Application\PublicMediaSource\PublicMediaReadResult;
use App\Application\PublicMediaSource\PublicMediaReadStatus;
use PDO;
use Throwable;

final readonly class PostgreSqlPublicMediaReader implements PublicMediaDecisionReader, PublicMediaRevisionReader
{
    public function __construct(private PDO $connection, private PostgreSqlPublicMediaMapper $mapper) {}

    public function read(string $mediaCollectionId): PublicMediaReadResult
    {
        $statement = $this->connection->prepare('SELECT media_collection_id,version,causation_key,revision_checksum,payload::text AS payload,payload_checksum FROM public_media.decisions WHERE media_collection_id=:media_collection_id');
        $statement->execute(['media_collection_id' => $mediaCollectionId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return PublicMediaReadResult::missing($mediaCollectionId);
        }

        try {
            return PublicMediaReadResult::found($mediaCollectionId, $this->mapper->toDecision($row));
        } catch (Throwable) {
            return PublicMediaReadResult::corrupted($mediaCollectionId);
        }
    }

    public function stableRevisionForMediaCollection(string $mediaCollectionId): ?PublicMediaRevision
    {
        $result = $this->read($mediaCollectionId);

        return $result->status === PublicMediaReadStatus::Found ? $result->decision?->revision : null;
    }
}

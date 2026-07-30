<?php

namespace App\Infrastructure\PublicMediaSource\PostgreSql;

use App\Application\PublicMediaSource\Contract\PublicMediaDecisionWriter;
use App\Application\PublicMediaSource\PublicMediaDecision;
use App\Application\PublicMediaSource\PublicMediaWriteResult;
use PDO;
use Throwable;

final readonly class PostgreSqlPublicMediaWriter implements PublicMediaDecisionWriter
{
    public function __construct(private PDO $connection, private PostgreSqlPublicMediaMapper $mapper) {}

    public function store(PublicMediaDecision $decision): PublicMediaWriteResult
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }

        try {
            $parameters = $this->mapper->parameters($decision);
            $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:media_collection_id,0))');
            $statement->execute(['media_collection_id' => $decision->mediaCollectionId]);
            $statement = $this->connection->prepare('SELECT version,revision_checksum,causation_key FROM public_media.decisions WHERE media_collection_id=:media_collection_id FOR UPDATE');
            $statement->execute(['media_collection_id' => $decision->mediaCollectionId]);
            $current = $statement->fetch(PDO::FETCH_ASSOC);

            if ($current !== false && $decision->revision->version->value < (int) $current['version']) {
                return $this->finish(PublicMediaWriteResult::RejectedObsolete, $owner);
            }
            if ($current !== false && $decision->revision->version->value === (int) $current['version']) {
                $same = hash_equals((string) $current['revision_checksum'], $parameters['revision_checksum'])
                    && (string) $current['causation_key'] === $parameters['causation'];

                return $this->finish($same ? PublicMediaWriteResult::AlreadyApplied : PublicMediaWriteResult::Divergent, $owner);
            }

            $statement = $this->connection->prepare('INSERT INTO public_media.decisions(media_collection_id,version,causation_key,revision_checksum,payload,payload_checksum) VALUES(:media_collection_id,:version,:causation,:revision_checksum,CAST(:payload AS jsonb),:payload_checksum) ON CONFLICT(media_collection_id) DO UPDATE SET version=EXCLUDED.version,causation_key=EXCLUDED.causation_key,revision_checksum=EXCLUDED.revision_checksum,payload=EXCLUDED.payload,payload_checksum=EXCLUDED.payload_checksum,updated_at=clock_timestamp()');
            $statement->execute($parameters);

            return $this->finish(PublicMediaWriteResult::Applied, $owner);
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            }

            throw $error;
        }
    }

    private function finish(PublicMediaWriteResult $result, bool $owner): PublicMediaWriteResult
    {
        if ($owner) {
            $this->connection->commit();
        }

        return $result;
    }
}

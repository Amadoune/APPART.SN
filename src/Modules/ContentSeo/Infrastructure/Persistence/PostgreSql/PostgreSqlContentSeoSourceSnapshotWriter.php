<?php

namespace Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ContentSeo\Application\Contract\ContentSeoSourceSnapshotWriter;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSnapshotWriteResult;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSourceDecision;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\ContentSeoSourceSnapshotMapper;
use PDO;
use Throwable;

final readonly class PostgreSqlContentSeoSourceSnapshotWriter implements ContentSeoSourceSnapshotWriter
{
    public function __construct(private PDO $connection, private ContentSeoSourceSnapshotMapper $mapper) {}

    public function store(ContentSeoSourceDecision $snapshot): ContentSeoSnapshotWriteResult
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }
        try {
            $parameters = $this->mapper->parameters($snapshot);
            $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:listing_id,0))');
            $statement->execute(['listing_id' => $snapshot->listingId->value]);
            $statement = $this->connection->prepare('SELECT version,payload_checksum FROM content_seo.public_source_snapshots WHERE listing_id=:listing_id FOR UPDATE');
            $statement->execute(['listing_id' => $snapshot->listingId->value]);
            $existing = $statement->fetch(PDO::FETCH_ASSOC);
            if ($existing !== false) {
                if ($snapshot->version < (int) $existing['version']) {
                    return $this->finish(ContentSeoSnapshotWriteResult::RejectedObsolete, $owner);
                }
                if ($snapshot->version === (int) $existing['version']) {
                    return $this->finish(hash_equals((string) $existing['payload_checksum'], $parameters['checksum']) ? ContentSeoSnapshotWriteResult::AlreadyApplied : ContentSeoSnapshotWriteResult::Divergent, $owner);
                }
            }
            $statement = $this->connection->prepare('INSERT INTO content_seo.public_source_snapshots(snapshot_id,listing_id,version,payload,payload_checksum) VALUES(:snapshot_id,:listing_id,:version,CAST(:payload AS jsonb),:checksum) ON CONFLICT(listing_id) DO UPDATE SET snapshot_id=EXCLUDED.snapshot_id,version=EXCLUDED.version,payload=EXCLUDED.payload,payload_checksum=EXCLUDED.payload_checksum,updated_at=clock_timestamp() WHERE EXCLUDED.version > public_source_snapshots.version');
            $statement->execute($parameters);
            if ($statement->rowCount() === 0) {
                $statement = $this->connection->prepare('SELECT version,payload_checksum FROM content_seo.public_source_snapshots WHERE listing_id=:listing_id FOR UPDATE');
                $statement->execute(['listing_id' => $snapshot->listingId->value]);
                $current = $statement->fetch(PDO::FETCH_ASSOC);
                if ($current !== false && $snapshot->version === (int) $current['version']) {
                    return $this->finish(hash_equals((string) $current['payload_checksum'], $parameters['checksum']) ? ContentSeoSnapshotWriteResult::AlreadyApplied : ContentSeoSnapshotWriteResult::Divergent, $owner);
                }

                return $this->finish(ContentSeoSnapshotWriteResult::RejectedObsolete, $owner);
            }

            return $this->finish(ContentSeoSnapshotWriteResult::Applied, $owner);
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            }
            throw $error;
        }
    }

    private function finish(ContentSeoSnapshotWriteResult $result, bool $owner): ContentSeoSnapshotWriteResult
    {
        if ($owner) {
            $this->connection->commit();
        }

        return $result;
    }
}

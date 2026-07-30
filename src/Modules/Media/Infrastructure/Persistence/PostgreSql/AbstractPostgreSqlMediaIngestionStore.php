<?php

namespace Appart\Modules\Media\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Media\Application\IngestionPersistence\AbstractIngestionState;
use Appart\Modules\Media\Application\IngestionPersistence\MediaIngestionPersistenceWriteResult;
use JsonException;
use PDO;
use PDOException;
use Throwable;

abstract readonly class AbstractPostgreSqlMediaIngestionStore
{
    public function __construct(protected PDO $connection) {}

    /** @return array<string, mixed>|null */
    protected function row(string $table, string $id): ?array
    {
        $statement = $this->connection->prepare("SELECT aggregate_id,state,version,last_intent_id,last_intent_checksum,payload::text AS payload FROM media_ingestion.$table WHERE aggregate_id=:id");
        $statement->execute(['id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /**
     * @param  callable(string): ?AbstractIngestionState  $reader
     *
     * @throws JsonException
     */
    protected function persist(string $table, string $intentTable, AbstractIngestionState $candidate, int $expectedVersion, callable $reader): MediaIngestionPersistenceWriteResult
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }
        try {
            $this->lock($table, $candidate->id, $candidate->intentId);
            $intent = $this->intent($intentTable, $candidate->intentId);
            if ($intent !== null) {
                $this->commit($owner);

                return hash_equals($intent, $candidate->intentChecksum)
                    ? MediaIngestionPersistenceWriteResult::AlreadyApplied
                    : MediaIngestionPersistenceWriteResult::DivergentIntent;
            }
            $current = $reader($candidate->id);
            $currentVersion = $current === null ? 0 : $current->version;
            if ($currentVersion !== $expectedVersion || $candidate->version !== $expectedVersion + 1) {
                $this->commit($owner);

                return MediaIngestionPersistenceWriteResult::VersionConflict;
            }
            $payload = json_encode((object) $candidate->payload, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);
            $sql = $current === null
                ? "INSERT INTO media_ingestion.$table(aggregate_id,state,version,last_intent_id,last_intent_checksum,payload) VALUES(:id,:state,:version,:intent_id,:checksum,CAST(:payload AS jsonb))"
                : "UPDATE media_ingestion.$table SET state=:state,version=:version,last_intent_id=:intent_id,last_intent_checksum=:checksum,payload=CAST(:payload AS jsonb),updated_at=CURRENT_TIMESTAMP WHERE aggregate_id=:id";
            $statement = $this->connection->prepare($sql);
            $statement->execute(['id' => $candidate->id, 'state' => $candidate->state, 'version' => $candidate->version, 'intent_id' => $candidate->intentId, 'checksum' => $candidate->intentChecksum, 'payload' => $payload]);
            $statement = $this->connection->prepare("INSERT INTO media_ingestion.$intentTable(intent_id,aggregate_id,checksum,result_version) VALUES(:intent_id,:id,:checksum,:version)");
            $statement->execute(['intent_id' => $candidate->intentId, 'id' => $candidate->id, 'checksum' => $candidate->intentChecksum, 'version' => $candidate->version]);
            $this->commit($owner);

            return MediaIngestionPersistenceWriteResult::Applied;
        } catch (PDOException $error) {
            $this->rollback($owner);

            return $error->getCode() === '23505'
                ? MediaIngestionPersistenceWriteResult::IdentityConflict
                : MediaIngestionPersistenceWriteResult::Rejected;
        } catch (Throwable $error) {
            $this->rollback($owner);
            throw $error;
        }
    }

    private function lock(string $owner, string $id, string $intentId): void
    {
        $statement = $this->connection->prepare("SELECT pg_advisory_xact_lock(hashtextextended(:owner || ':' || :id,0)), pg_advisory_xact_lock(hashtextextended(:owner || ':intent:' || :intent,0))");
        $statement->execute(['owner' => $owner, 'id' => $id, 'intent' => $intentId]);
    }

    private function intent(string $table, string $intentId): ?string
    {
        $statement = $this->connection->prepare("SELECT checksum FROM media_ingestion.$table WHERE intent_id=:intent_id");
        $statement->execute(['intent_id' => $intentId]);
        $checksum = $statement->fetchColumn();

        return $checksum === false ? null : (string) $checksum;
    }

    private function commit(bool $owner): void
    {
        if ($owner) {
            $this->connection->commit();
        }
    }

    private function rollback(bool $owner): void
    {
        if ($owner && $this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }
}

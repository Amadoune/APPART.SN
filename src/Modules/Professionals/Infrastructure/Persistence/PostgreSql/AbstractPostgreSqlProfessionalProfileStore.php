<?php

namespace Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalProfileWriteResult;
use PDO;
use Throwable;

abstract readonly class AbstractPostgreSqlProfessionalProfileStore
{
    public function __construct(protected PDO $connection) {}

    protected function lock(string $professionalId): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:id,0))');
        $statement->execute(['id' => $professionalId]);
    }

    protected function intent(string $table, string $professionalId, string $intentId, string $checksum): ?ProfessionalProfileWriteResult
    {
        $statement = $this->connection->prepare("SELECT intent_checksum FROM professional_profile.{$table} WHERE professional_id=CAST(:id AS uuid) AND intent_id=CAST(:intent AS uuid)");
        $statement->execute(['id' => $professionalId, 'intent' => $intentId]);
        $found = $statement->fetchColumn();
        if (! is_string($found)) {
            return null;
        }

        return hash_equals($found, $checksum)
            ? ProfessionalProfileWriteResult::AlreadyApplied
            : ProfessionalProfileWriteResult::DivergentIntent;
    }

    protected function recordIntent(string $table, string $professionalId, string $intentId, string $checksum, string $at): void
    {
        $statement = $this->connection->prepare("INSERT INTO professional_profile.{$table}(professional_id,intent_id,intent_checksum,recorded_at) VALUES(CAST(:id AS uuid),CAST(:intent AS uuid),:checksum,CAST(:at AS timestamptz))");
        $statement->execute(['id' => $professionalId, 'intent' => $intentId, 'checksum' => $checksum, 'at' => $at]);
    }

    /**
     * @template T
     *
     * @param  callable(): T  $operation
     * @return T
     */
    protected function transaction(callable $operation): mixed
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }
        try {
            $result = $operation();
            if ($owner) {
                $this->connection->commit();
            }

            return $result;
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            }
            throw $error;
        }
    }
}

<?php

namespace Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\Contract\ProfessionalMandateOwnerSource;
use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\Contract\ProfessionalMandateOwnerSourceUpdater;
use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\ProfessionalMandateOwnerSourceResult;
use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\ProfessionalMandateOwnerSourceState;
use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\ProfessionalMandateOwnerSourceWriteResult;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalMandateOwnerSourceMapper;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlProfessionalMandateOwnerSource implements ProfessionalMandateOwnerSource, ProfessionalMandateOwnerSourceUpdater
{
    public function __construct(
        private PDO $connection,
        private ProfessionalMandateOwnerSourceMapper $mapper,
    ) {}

    public function resolve(string $accountId): ProfessionalMandateOwnerSourceResult
    {
        try {
            $statement = $this->connection->prepare(
                'SELECT professional_ids FROM professional_core.mandate_owner_sources WHERE account_id=CAST(:account_id AS uuid)',
            );
            $statement->execute(['account_id' => $accountId]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                return ProfessionalMandateOwnerSourceResult::notMandated();
            }
            $decoded = json_decode((string) $row['professional_ids'], true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($decoded) || array_is_list($decoded) === false) {
                return ProfessionalMandateOwnerSourceResult::corrupted();
            }
            $ids = [];
            foreach ($decoded as $value) {
                if (! is_string($value)) {
                    return ProfessionalMandateOwnerSourceResult::corrupted();
                }
                $ids[] = $value;
            }
            $canonical = array_values(array_unique($ids));
            sort($canonical, SORT_STRING);
            if ($canonical !== $ids) {
                return ProfessionalMandateOwnerSourceResult::corrupted();
            }
            if (count($ids) === 1 && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $ids[0]) !== 1) {
                return ProfessionalMandateOwnerSourceResult::corrupted();
            }

            return match (count($ids)) {
                0 => ProfessionalMandateOwnerSourceResult::notMandated(),
                1 => ProfessionalMandateOwnerSourceResult::resolved(ProfessionalId::fromString($ids[0])),
                default => ProfessionalMandateOwnerSourceResult::ambiguous(),
            };
        } catch (Throwable) {
            return ProfessionalMandateOwnerSourceResult::dependencyUnavailable();
        }
    }

    public function replace(
        ProfessionalMandateOwnerSourceState $candidate,
        int $expectedVersion,
    ): ProfessionalMandateOwnerSourceWriteResult {
        try {
            return $this->transaction(function () use ($candidate, $expectedVersion): ProfessionalMandateOwnerSourceWriteResult {
                $parameters = $this->mapper->parameters($candidate);
                $this->lock($candidate->accountId);
                $intent = $this->intent($candidate->accountId, $candidate->intentId, $candidate->intentChecksum);
                if ($intent !== null) {
                    return $intent;
                }
                $version = $this->version($candidate->accountId);
                if ($version !== $expectedVersion || $candidate->version !== $expectedVersion + 1) {
                    return ProfessionalMandateOwnerSourceWriteResult::VersionConflict;
                }

                $statement = $this->connection->prepare(
                    'INSERT INTO professional_core.mandate_owner_sources(account_id,professional_ids,version,last_intent_id,last_intent_checksum,recorded_at)
                     VALUES(CAST(:account_id AS uuid),CAST(:professional_ids AS jsonb),:version,CAST(:intent_id AS uuid),:intent_checksum,CAST(:recorded_at AS timestamptz))
                     ON CONFLICT(account_id) DO UPDATE SET professional_ids=EXCLUDED.professional_ids,version=EXCLUDED.version,last_intent_id=EXCLUDED.last_intent_id,last_intent_checksum=EXCLUDED.last_intent_checksum,recorded_at=EXCLUDED.recorded_at',
                );
                $statement->execute($parameters);
                $intentStatement = $this->connection->prepare(
                    'INSERT INTO professional_core.mandate_owner_source_intents(account_id,intent_id,intent_checksum,recorded_at)
                     VALUES(CAST(:account_id AS uuid),CAST(:intent_id AS uuid),:intent_checksum,CAST(:recorded_at AS timestamptz))',
                );
                $intentStatement->execute([
                    'account_id' => $parameters['account_id'],
                    'intent_id' => $parameters['intent_id'],
                    'intent_checksum' => $parameters['intent_checksum'],
                    'recorded_at' => $parameters['recorded_at'],
                ]);

                return ProfessionalMandateOwnerSourceWriteResult::Applied;
            });
        } catch (PDOException) {
            return ProfessionalMandateOwnerSourceWriteResult::Rejected;
        }
    }

    private function lock(string $accountId): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:account_id,0))');
        $statement->execute(['account_id' => $accountId]);
    }

    private function version(string $accountId): int
    {
        $statement = $this->connection->prepare(
            'SELECT version FROM professional_core.mandate_owner_sources WHERE account_id=CAST(:account_id AS uuid)',
        );
        $statement->execute(['account_id' => $accountId]);
        $version = $statement->fetchColumn();

        return $version === false ? 0 : (int) $version;
    }

    private function intent(string $accountId, string $intentId, string $checksum): ?ProfessionalMandateOwnerSourceWriteResult
    {
        $statement = $this->connection->prepare(
            'SELECT intent_checksum FROM professional_core.mandate_owner_source_intents WHERE account_id=CAST(:account_id AS uuid) AND intent_id=CAST(:intent_id AS uuid)',
        );
        $statement->execute(['account_id' => $accountId, 'intent_id' => $intentId]);
        $stored = $statement->fetchColumn();
        if ($stored === false) {
            return null;
        }

        return hash_equals((string) $stored, $checksum)
            ? ProfessionalMandateOwnerSourceWriteResult::AlreadyApplied
            : ProfessionalMandateOwnerSourceWriteResult::DivergentIntent;
    }

    /**
     * @template T
     *
     * @param  callable(): T  $operation
     * @return T
     */
    private function transaction(callable $operation): mixed
    {
        $owner = ! $this->connection->inTransaction();
        $savepoint = 'professional_mandate_owner_source';
        if ($owner) {
            $this->connection->beginTransaction();
        } else {
            $this->connection->exec("SAVEPOINT {$savepoint}");
        }
        try {
            $result = $operation();
            if ($owner) {
                $this->connection->commit();
            } else {
                $this->connection->exec("RELEASE SAVEPOINT {$savepoint}");
            }

            return $result;
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            } else {
                $this->connection->exec("ROLLBACK TO SAVEPOINT {$savepoint}");
            }
            throw $error;
        }
    }
}

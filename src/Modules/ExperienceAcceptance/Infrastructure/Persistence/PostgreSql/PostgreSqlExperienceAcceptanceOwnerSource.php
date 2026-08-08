<?php

namespace Appart\Modules\ExperienceAcceptance\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceOwnerSource;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceReadResult;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceRevisionState;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceScopeKey;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceStream;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceWriteResult;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;
use Appart\Modules\ExperienceAcceptance\Infrastructure\Persistence\ExperienceAcceptanceOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

/** @phpstan-type OwnerRow array{scope_key:string,stream_type:string,revision:int,decision:string,effective_at:string,recorded_at:string,revision_checksum:string} */
final readonly class PostgreSqlExperienceAcceptanceOwnerSource implements ExperienceAcceptanceOwnerSource
{
    private const SAVEPOINT = 'experience_acceptance_owner_source';

    public function __construct(private PDO $connection, private ExperienceAcceptanceOwnerSourceMapper $mapper) {}

    public function append(ExperienceAcceptanceRevisionState $revision): ExperienceAcceptanceWriteResult
    {
        try {
            return ExperienceAcceptanceWriteResult::from($this->appendRow($this->mapper->toRow($revision)));
        } catch (PDOException) {
            return ExperienceAcceptanceWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return ExperienceAcceptanceWriteResult::Corrupted;
        }
    }

    public function read(ExperienceAcceptanceScopeKey $scope, ExperienceAcceptanceStream $stream, ExperienceAcceptanceObservedAt $observedAt): ExperienceAcceptanceReadResult
    {
        try {
            $row = $this->temporalRow($scope->value, $stream->value, $observedAt->canonical());

            return $row === false ? ExperienceAcceptanceReadResult::missing() : ExperienceAcceptanceReadResult::found($this->mapper->toState($row));
        } catch (PDOException) {
            return ExperienceAcceptanceReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return ExperienceAcceptanceReadResult::corrupted();
        }
    }

    /** @param OwnerRow $row */
    private function appendRow(array $row): string
    {
        return $this->transactional(function () use ($row): string {
            $this->lockStream($row['scope_key'], $row['stream_type']);
            $current = $this->currentRow($row['scope_key'], $row['stream_type']);
            if ($current === false) {
                if ($row['revision'] !== 1) {
                    return 'version_conflict';
                }
            } else {
                if ($row['revision'] <= $current['revision']) {
                    return $this->classifyExisting($row);
                }
                if ($row['revision'] !== $current['revision'] + 1 || new DateTimeImmutable($row['effective_at']) <= new DateTimeImmutable($current['effective_at']) || new DateTimeImmutable($row['recorded_at']) < new DateTimeImmutable($current['recorded_at'])) {
                    return 'version_conflict';
                }
            }

            $result = $this->insertOrClassify($row);
            if ($result === 'applied') {
                $this->updateIndex($row);
            }

            return $result;
        });
    }

    /** @return OwnerRow|false */
    private function temporalRow(string $scope, string $stream, string $at): array|false
    {
        $query = $this->connection->prepare($this->selectSql().' WHERE scope_key=:scope AND stream_type=:stream AND effective_at<=CAST(:at AS timestamptz) AND recorded_at<=CAST(:at AS timestamptz) ORDER BY effective_at DESC,recorded_at DESC,revision DESC LIMIT 1');
        $query->execute(['scope' => $scope, 'stream' => $stream, 'at' => $at]);

        /** @var OwnerRow|false */ return $query->fetch(PDO::FETCH_ASSOC);
    }

    private function transactional(callable $operation): string
    {
        $ownsTransaction = ! $this->connection->inTransaction();
        $ownsTransaction ? $this->connection->beginTransaction() : $this->connection->exec('SAVEPOINT '.self::SAVEPOINT);
        try {
            $result = $operation();
            $ownsTransaction ? $this->connection->commit() : $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);

            return $result;
        } catch (Throwable $exception) {
            if ($ownsTransaction) {
                $this->connection->rollBack();
            } else {
                $this->connection->exec('ROLLBACK TO SAVEPOINT '.self::SAVEPOINT);
                $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);
            }
            throw $exception;
        }
    }

    private function lockStream(string $scope, string $stream): void
    {
        $query = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:key,0))');
        $query->execute(['key' => $scope."\n".$stream]);
    }

    /** @param OwnerRow $row */
    private function insertOrClassify(array $row): string
    {
        $query = $this->connection->prepare('INSERT INTO experience_acceptance.owner_revision_journal(scope_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum) VALUES(:scope_key,:stream_type,:revision,:decision,CAST(:effective_at AS timestamptz),CAST(:recorded_at AS timestamptz),:revision_checksum) ON CONFLICT(scope_key,stream_type,revision) DO NOTHING');
        $query->execute($row);

        return $query->rowCount() === 1 ? 'applied' : $this->classifyExisting($row);
    }

    /** @param OwnerRow $row */
    private function updateIndex(array $row): void
    {
        $query = $this->connection->prepare('INSERT INTO experience_acceptance.owner_current_index(scope_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum) VALUES(:scope_key,:stream_type,:revision,:decision,CAST(:effective_at AS timestamptz),CAST(:recorded_at AS timestamptz),:revision_checksum) ON CONFLICT(scope_key,stream_type) DO UPDATE SET revision=EXCLUDED.revision,decision=EXCLUDED.decision,effective_at=EXCLUDED.effective_at,recorded_at=EXCLUDED.recorded_at,revision_checksum=EXCLUDED.revision_checksum WHERE EXCLUDED.revision>owner_current_index.revision');
        $query->execute($row);
        if ($query->rowCount() !== 1) {
            throw new RuntimeException('Experience Acceptance current index did not converge.');
        }
    }

    /** @param OwnerRow $row */
    private function classifyExisting(array $row): string
    {
        $query = $this->connection->prepare('SELECT revision_checksum FROM experience_acceptance.owner_revision_journal WHERE scope_key=:scope_key AND stream_type=:stream_type AND revision=:revision');
        $query->execute(['scope_key' => $row['scope_key'], 'stream_type' => $row['stream_type'], 'revision' => $row['revision']]);
        $checksum = $query->fetchColumn();
        if (! is_string($checksum)) {
            return 'version_conflict';
        }

        return hash_equals($checksum, $row['revision_checksum']) ? 'already_applied' : 'divergent_revision';
    }

    /** @return OwnerRow|false */
    private function currentRow(string $scope, string $stream): array|false
    {
        $query = $this->connection->prepare($this->selectSql().' WHERE scope_key=:scope AND stream_type=:stream ORDER BY revision DESC LIMIT 1 FOR UPDATE');
        $query->execute(['scope' => $scope, 'stream' => $stream]);

        /** @var OwnerRow|false */ return $query->fetch(PDO::FETCH_ASSOC);
    }

    private function selectSql(): string
    {
        return 'SELECT scope_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum FROM experience_acceptance.owner_revision_journal';
    }
}

<?php

namespace Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\Contract\SearchQueryResolutionOwnerSource;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionReadResult;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionRevisionState;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionWriteResult;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchQueryResolutionOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final readonly class PostgreSqlSearchQueryResolutionOwnerSource implements SearchQueryResolutionOwnerSource
{
    private const SAVEPOINT = 'search_query_resolution_owner_source';

    public function __construct(private PDO $connection, private SearchQueryResolutionOwnerSourceMapper $mapper) {}

    public function append(SearchQueryResolutionRevisionState $revision): SearchQueryResolutionWriteResult
    {
        try {
            return $this->transactional(function () use ($revision): SearchQueryResolutionWriteResult {
                $this->lockQuery($revision->queryFingerprint);
                $row = $this->mapper->toRow($revision);
                $current = $this->currentRow($revision->queryFingerprint, true);
                if ($current === false) {
                    if ($revision->revision !== 1) {
                        return SearchQueryResolutionWriteResult::VersionConflict;
                    }
                } else {
                    $currentRevision = (int) $current['revision'];
                    if ($revision->revision <= $currentRevision) {
                        return $this->classifyExisting($row);
                    }
                    if ($revision->revision !== $currentRevision + 1
                        || $revision->effectiveAt <= new DateTimeImmutable((string) $current['effective_at'])
                        || $revision->recordedAt < new DateTimeImmutable((string) $current['recorded_at'])) {
                        return SearchQueryResolutionWriteResult::VersionConflict;
                    }
                }
                $result = $this->insertOrClassify($row);
                if ($result === SearchQueryResolutionWriteResult::Applied) {
                    $this->updateCurrentIndex($row);
                }

                return $result;
            });
        } catch (PDOException) {
            return SearchQueryResolutionWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return SearchQueryResolutionWriteResult::Corrupted;
        }
    }

    public function read(SearchQuery $query, SearchQueryResolutionObservedAt $observedAt): SearchQueryResolutionReadResult
    {
        try {
            $statement = $this->connection->prepare($this->selectSql().' WHERE query_fingerprint=:query_fingerprint
                AND effective_at<=CAST(:observed_at AS timestamptz) AND recorded_at<=CAST(:observed_at AS timestamptz)
                ORDER BY revision DESC LIMIT 1');
            $statement->execute(['query_fingerprint' => SearchQueryResolutionRevisionState::fingerprint($query), 'observed_at' => $observedAt->canonical()]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);

            return $row === false ? SearchQueryResolutionReadResult::missing($query) : SearchQueryResolutionReadResult::found($this->mapper->toState($row));
        } catch (PDOException) {
            return SearchQueryResolutionReadResult::dependencyUnavailable($query);
        } catch (Throwable) {
            return SearchQueryResolutionReadResult::corrupted($query);
        }
    }

    public function history(SearchQuery $query): array
    {
        $fingerprint = SearchQueryResolutionRevisionState::fingerprint($query);
        $statement = $this->connection->prepare($this->selectSql().' WHERE query_fingerprint=:query_fingerprint ORDER BY revision ASC');
        $statement->execute(['query_fingerprint' => $fingerprint]);
        $states = [];
        $expected = 1;
        $previous = null;
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $state = $this->mapper->toState($row);
            if ($state->revision !== $expected || ($previous !== null && ($state->effectiveAt <= $previous->effectiveAt || $state->recordedAt < $previous->recordedAt))) {
                throw new RuntimeException('Corrupted Search query resolution chronology.');
            }
            $states[] = $state;
            $previous = $state;
            $expected++;
        }

        return $states;
    }

    /** @param callable(): SearchQueryResolutionWriteResult $operation */
    private function transactional(callable $operation): SearchQueryResolutionWriteResult
    {
        $owner = ! $this->connection->inTransaction();
        $owner ? $this->connection->beginTransaction() : $this->connection->exec('SAVEPOINT '.self::SAVEPOINT);
        try {
            $result = $operation();
            $owner ? $this->connection->commit() : $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);

            return $result;
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            } else {
                $this->connection->exec('ROLLBACK TO SAVEPOINT '.self::SAVEPOINT);
                $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);
            }
            throw $error;
        }
    }

    private function lockQuery(string $fingerprint): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:fingerprint,0))');
        $statement->execute(['fingerprint' => $fingerprint]);
    }

    /** @param array<string, mixed> $row */
    private function insertOrClassify(array $row): SearchQueryResolutionWriteResult
    {
        $statement = $this->connection->prepare('INSERT INTO search_discovery.search_query_resolution_revision_journal
            (query_fingerprint,revision,decision,effective_at,recorded_at,revision_checksum)
            VALUES(:query_fingerprint,:revision,:decision,CAST(:effective_at AS timestamptz),CAST(:recorded_at AS timestamptz),:revision_checksum)
            ON CONFLICT (query_fingerprint,revision) DO NOTHING');
        $statement->execute($row);

        return $statement->rowCount() === 1 ? SearchQueryResolutionWriteResult::Applied : $this->classifyExisting($row);
    }

    /** @param array<string, mixed> $row */
    private function updateCurrentIndex(array $row): void
    {
        $statement = $this->connection->prepare('INSERT INTO search_discovery.search_query_resolution_current_index
            (query_fingerprint,revision,decision,effective_at,recorded_at,revision_checksum)
            VALUES(:query_fingerprint,:revision,:decision,CAST(:effective_at AS timestamptz),CAST(:recorded_at AS timestamptz),:revision_checksum)
            ON CONFLICT (query_fingerprint) DO UPDATE SET revision=EXCLUDED.revision,decision=EXCLUDED.decision,
            effective_at=EXCLUDED.effective_at,recorded_at=EXCLUDED.recorded_at,revision_checksum=EXCLUDED.revision_checksum
            WHERE EXCLUDED.revision > search_query_resolution_current_index.revision');
        $statement->execute($row);
        if ($statement->rowCount() !== 1) {
            throw new RuntimeException('Search query resolution current index did not converge.');
        }
    }

    /** @param array<string, mixed> $row */
    private function classifyExisting(array $row): SearchQueryResolutionWriteResult
    {
        $statement = $this->connection->prepare('SELECT revision_checksum FROM search_discovery.search_query_resolution_revision_journal WHERE query_fingerprint=:query_fingerprint AND revision=:revision');
        $statement->execute(['query_fingerprint' => $row['query_fingerprint'], 'revision' => $row['revision']]);
        $checksum = $statement->fetchColumn();
        if (! is_string($checksum)) {
            return SearchQueryResolutionWriteResult::VersionConflict;
        }

        return hash_equals($checksum, (string) $row['revision_checksum']) ? SearchQueryResolutionWriteResult::AlreadyApplied : SearchQueryResolutionWriteResult::DivergentRevision;
    }

    /** @return array<string, mixed>|false */
    private function currentRow(string $fingerprint, bool $forUpdate): array|false
    {
        $sql = $this->selectSql().' WHERE query_fingerprint=:query_fingerprint ORDER BY revision DESC LIMIT 1'.($forUpdate ? ' FOR UPDATE' : '');
        $statement = $this->connection->prepare($sql);
        $statement->execute(['query_fingerprint' => $fingerprint]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    private function selectSql(): string
    {
        return 'SELECT query_fingerprint,revision,decision,effective_at,recorded_at,revision_checksum FROM search_discovery.search_query_resolution_revision_journal';
    }
}

<?php

namespace Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\Contract\SearchOwnerSource;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerReadResult;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerRevisionState;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerWriteResult;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final readonly class PostgreSqlSearchOwnerSource implements SearchOwnerSource
{
    private const SAVEPOINT = 'search_owner_source';

    public function __construct(private PDO $connection, private SearchOwnerSourceMapper $mapper) {}

    public function append(SearchOwnerRevisionState $revision): SearchOwnerWriteResult
    {
        try {
            return $this->transactional(function () use ($revision): SearchOwnerWriteResult {
                $this->lockDocument($revision->documentId);
                $row = $this->mapper->toRow($revision);
                $current = $this->currentRow($revision->documentId, true);
                if ($current === false) {
                    if ($revision->revision !== 1) {
                        return SearchOwnerWriteResult::VersionConflict;
                    }
                } else {
                    $currentRevision = (int) $current['revision'];
                    if ($revision->revision <= $currentRevision) {
                        return $this->classifyExisting($row);
                    }
                    if ($revision->revision !== $currentRevision + 1
                        || $revision->effectiveAt <= new DateTimeImmutable((string) $current['effective_at'])
                        || $revision->recordedAt < new DateTimeImmutable((string) $current['recorded_at'])) {
                        return SearchOwnerWriteResult::VersionConflict;
                    }
                }

                $result = $this->insertOrClassify($row);
                if ($result === SearchOwnerWriteResult::Applied) {
                    $this->updateCurrentIndex($row);
                }

                return $result;
            });
        } catch (PDOException) {
            return SearchOwnerWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return SearchOwnerWriteResult::Corrupted;
        }
    }

    public function read(SearchDocumentId $documentId, SearchObservedAt $observedAt): SearchOwnerReadResult
    {
        try {
            $statement = $this->connection->prepare(
                $this->selectSql().' WHERE document_id=:document_id
                 AND effective_at<=CAST(:observed_at AS timestamptz)
                 AND recorded_at<=CAST(:observed_at AS timestamptz)
                 ORDER BY revision DESC LIMIT 1',
            );
            $statement->execute([
                'document_id' => $documentId->value,
                'observed_at' => $observedAt->canonical(),
            ]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);

            return $row === false
                ? SearchOwnerReadResult::missing($documentId)
                : SearchOwnerReadResult::found($this->mapper->toState($row));
        } catch (PDOException) {
            return SearchOwnerReadResult::dependencyUnavailable($documentId);
        } catch (Throwable) {
            return SearchOwnerReadResult::corrupted($documentId);
        }
    }

    public function history(SearchDocumentId $documentId): array
    {
        $statement = $this->connection->prepare($this->selectSql().' WHERE document_id=:document_id ORDER BY revision ASC');
        $statement->execute(['document_id' => $documentId->value]);
        $states = [];
        $expected = 1;
        $previous = null;
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $state = $this->mapper->toState($row);
            if ($state->revision !== $expected
                || ($previous !== null && ($state->effectiveAt <= $previous->effectiveAt || $state->recordedAt < $previous->recordedAt))) {
                throw new RuntimeException('Corrupted Search owner revision chronology.');
            }
            $states[] = $state;
            $previous = $state;
            $expected++;
        }

        return $states;
    }

    /** @param callable(): SearchOwnerWriteResult $operation */
    private function transactional(callable $operation): SearchOwnerWriteResult
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        } else {
            $this->connection->exec('SAVEPOINT '.self::SAVEPOINT);
        }
        try {
            $result = $operation();
            if ($owner) {
                $this->connection->commit();
            } else {
                $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);
            }

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

    private function lockDocument(SearchDocumentId $documentId): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:document_id,0))');
        $statement->execute(['document_id' => $documentId->value]);
    }

    /** @param array<string, mixed> $row */
    private function insertOrClassify(array $row): SearchOwnerWriteResult
    {
        $statement = $this->connection->prepare(
            'INSERT INTO search_discovery.search_owner_revision_journal
             (document_id,revision,decision_id,listing_id,state,payload,payload_checksum,effective_at,recorded_at,revision_checksum)
             VALUES(CAST(:document_id AS uuid),:revision,CAST(:decision_id AS uuid),CAST(:listing_id AS uuid),:state,CAST(:payload AS jsonb),:payload_checksum,CAST(:effective_at AS timestamptz),CAST(:recorded_at AS timestamptz),:revision_checksum)
             ON CONFLICT (document_id,revision) DO NOTHING',
        );
        $statement->execute($row);

        return $statement->rowCount() === 1 ? SearchOwnerWriteResult::Applied : $this->classifyExisting($row);
    }

    /** @param array<string, mixed> $row */
    private function updateCurrentIndex(array $row): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO search_discovery.search_owner_current_index
             (document_id,revision,decision_id,listing_id,state,payload,payload_checksum,effective_at,recorded_at,revision_checksum)
             VALUES(CAST(:document_id AS uuid),:revision,CAST(:decision_id AS uuid),CAST(:listing_id AS uuid),:state,CAST(:payload AS jsonb),:payload_checksum,CAST(:effective_at AS timestamptz),CAST(:recorded_at AS timestamptz),:revision_checksum)
             ON CONFLICT (document_id) DO UPDATE SET revision=EXCLUDED.revision,decision_id=EXCLUDED.decision_id,listing_id=EXCLUDED.listing_id,state=EXCLUDED.state,payload=EXCLUDED.payload,payload_checksum=EXCLUDED.payload_checksum,effective_at=EXCLUDED.effective_at,recorded_at=EXCLUDED.recorded_at,revision_checksum=EXCLUDED.revision_checksum
             WHERE EXCLUDED.revision > search_owner_current_index.revision',
        );
        $statement->execute($row);
        if ($statement->rowCount() !== 1) {
            throw new RuntimeException('Search current index did not converge.');
        }
    }

    /** @param array<string, mixed> $row */
    private function classifyExisting(array $row): SearchOwnerWriteResult
    {
        $statement = $this->connection->prepare(
            'SELECT revision_checksum FROM search_discovery.search_owner_revision_journal
             WHERE document_id=:document_id AND revision=:revision',
        );
        $statement->execute(['document_id' => $row['document_id'], 'revision' => $row['revision']]);
        $checksum = $statement->fetchColumn();
        if (! is_string($checksum)) {
            return SearchOwnerWriteResult::VersionConflict;
        }

        return hash_equals($checksum, (string) $row['revision_checksum'])
            ? SearchOwnerWriteResult::AlreadyApplied
            : SearchOwnerWriteResult::DivergentRevision;
    }

    /** @return array<string, mixed>|false */
    private function currentRow(SearchDocumentId $documentId, bool $forUpdate): array|false
    {
        $sql = $this->selectSql().' WHERE document_id=:document_id ORDER BY revision DESC LIMIT 1';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }
        $statement = $this->connection->prepare($sql);
        $statement->execute(['document_id' => $documentId->value]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    private function selectSql(): string
    {
        return 'SELECT document_id::text,revision,decision_id::text,listing_id::text,state,payload::text,payload_checksum,effective_at,recorded_at,revision_checksum
                FROM search_discovery.search_owner_revision_journal';
    }
}

<?php

namespace Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ContentSeo\Application\OwnerSource\ContentSeoOwnerSource;
use Appart\Modules\ContentSeo\Application\OwnerSource\EditorialContentReadResult;
use Appart\Modules\ContentSeo\Application\OwnerSource\EditorialContentRevisionState;
use Appart\Modules\ContentSeo\Application\OwnerSource\EditorialContentWriteResult;
use Appart\Modules\ContentSeo\Application\OwnerSource\OperationalSeoReadResult;
use Appart\Modules\ContentSeo\Application\OwnerSource\OperationalSeoRevisionState;
use Appart\Modules\ContentSeo\Application\OwnerSource\OperationalSeoWriteResult;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoObservedAt;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoPublicResourceKey;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\ContentSeoOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final readonly class PostgreSqlContentSeoOwnerSource implements ContentSeoOwnerSource
{
    private const SAVEPOINT = 'content_seo_owner_source';

    public function __construct(private PDO $connection, private ContentSeoOwnerSourceMapper $mapper) {}

    public function appendEditorial(EditorialContentRevisionState $revision): EditorialContentWriteResult
    {
        try {
            return EditorialContentWriteResult::from($this->appendRow($this->mapper->editorialToRow($revision)));
        } catch (PDOException) {
            return EditorialContentWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return EditorialContentWriteResult::Corrupted;
        }
    }

    public function appendOperationalSeo(OperationalSeoRevisionState $revision): OperationalSeoWriteResult
    {
        try {
            return OperationalSeoWriteResult::from($this->appendRow($this->mapper->operationalSeoToRow($revision)));
        } catch (PDOException) {
            return OperationalSeoWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return OperationalSeoWriteResult::Corrupted;
        }
    }

    public function readEditorial(ContentSeoPublicResourceKey $resource, ContentSeoObservedAt $observedAt): EditorialContentReadResult
    {
        try {
            $row = $this->temporalRow($resource->canonical(), 'editorial', $observedAt->canonical());

            return $row === false ? EditorialContentReadResult::missing() : EditorialContentReadResult::found($this->mapper->toEditorialState($row));
        } catch (PDOException) {
            return EditorialContentReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return EditorialContentReadResult::corrupted();
        }
    }

    public function readOperationalSeo(ContentSeoPublicResourceKey $resource, ContentSeoObservedAt $observedAt): OperationalSeoReadResult
    {
        try {
            $row = $this->temporalRow($resource->canonical(), 'operational_seo', $observedAt->canonical());

            return $row === false ? OperationalSeoReadResult::missing() : OperationalSeoReadResult::found($this->mapper->toOperationalSeoState($row));
        } catch (PDOException) {
            return OperationalSeoReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return OperationalSeoReadResult::corrupted();
        }
    }

    public function editorialHistory(ContentSeoPublicResourceKey $resource): array
    {
        return array_map($this->mapper->toEditorialState(...), $this->historyRows($resource->canonical(), 'editorial'));
    }

    public function operationalSeoHistory(ContentSeoPublicResourceKey $resource): array
    {
        return array_map($this->mapper->toOperationalSeoState(...), $this->historyRows($resource->canonical(), 'operational_seo'));
    }

    /** @param array<string, mixed> $row */
    private function appendRow(array $row): string
    {
        return $this->transactional(function () use ($row): string {
            $this->lockStream((string) $row['resource_key'], (string) $row['stream_type']);
            $current = $this->currentRow((string) $row['resource_key'], (string) $row['stream_type'], true);
            if ($current === false) {
                if ((int) $row['revision'] !== 1) {
                    return 'version_conflict';
                }
            } else {
                $currentRevision = (int) $current['revision'];
                if ((int) $row['revision'] <= $currentRevision) {
                    return $this->classifyExisting($row);
                }
                if ((int) $row['revision'] !== $currentRevision + 1
                    || new DateTimeImmutable((string) $row['effective_at']) <= new DateTimeImmutable((string) $current['effective_at'])
                    || new DateTimeImmutable((string) $row['recorded_at']) < new DateTimeImmutable((string) $current['recorded_at'])) {
                    return 'version_conflict';
                }
            }
            $result = $this->insertOrClassify($row);
            if ($result === 'applied') {
                $this->updateCurrentIndex($row);
            }

            return $result;
        });
    }

    /** @return array<string, mixed>|false */
    private function temporalRow(string $resource, string $stream, string $observedAt): array|false
    {
        $statement = $this->connection->prepare($this->selectSql().' WHERE resource_key=:resource_key AND stream_type=:stream_type AND effective_at<=CAST(:observed_at AS timestamptz) AND recorded_at<=CAST(:observed_at AS timestamptz) ORDER BY revision DESC LIMIT 1');
        $statement->execute(['resource_key' => $resource, 'stream_type' => $stream, 'observed_at' => $observedAt]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /** @return list<array<string, mixed>> */
    private function historyRows(string $resource, string $stream): array
    {
        $statement = $this->connection->prepare($this->selectSql().' WHERE resource_key=:resource_key AND stream_type=:stream_type ORDER BY revision ASC');
        $statement->execute(['resource_key' => $resource, 'stream_type' => $stream]);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $expected = 1;
        $previous = null;
        foreach ($rows as $row) {
            if ((int) $row['revision'] !== $expected || ($previous !== null && (new DateTimeImmutable((string) $row['effective_at']) <= new DateTimeImmutable((string) $previous['effective_at']) || new DateTimeImmutable((string) $row['recorded_at']) < new DateTimeImmutable((string) $previous['recorded_at'])))) {
                throw new RuntimeException('Corrupted ContentSeo owner source chronology.');
            }
            $previous = $row;
            $expected++;
        }

        return $rows;
    }

    /** @param callable(): string $operation */
    private function transactional(callable $operation): string
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

    private function lockStream(string $resource, string $stream): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:stream_key,0))');
        $statement->execute(['stream_key' => $resource."\n".$stream]);
    }

    /** @param array<string, mixed> $row */
    private function insertOrClassify(array $row): string
    {
        $statement = $this->connection->prepare('INSERT INTO content_seo.editorial_seo_revision_journal (resource_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum) VALUES(:resource_key,:stream_type,:revision,:decision,CAST(:effective_at AS timestamptz),CAST(:recorded_at AS timestamptz),:revision_checksum) ON CONFLICT (resource_key,stream_type,revision) DO NOTHING');
        $statement->execute($row);

        return $statement->rowCount() === 1 ? 'applied' : $this->classifyExisting($row);
    }

    /** @param array<string, mixed> $row */
    private function updateCurrentIndex(array $row): void
    {
        $statement = $this->connection->prepare('INSERT INTO content_seo.editorial_seo_current_index (resource_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum) VALUES(:resource_key,:stream_type,:revision,:decision,CAST(:effective_at AS timestamptz),CAST(:recorded_at AS timestamptz),:revision_checksum) ON CONFLICT (resource_key,stream_type) DO UPDATE SET revision=EXCLUDED.revision,decision=EXCLUDED.decision,effective_at=EXCLUDED.effective_at,recorded_at=EXCLUDED.recorded_at,revision_checksum=EXCLUDED.revision_checksum WHERE EXCLUDED.revision > editorial_seo_current_index.revision');
        $statement->execute($row);
        if ($statement->rowCount() !== 1) {
            throw new RuntimeException('ContentSeo current index did not converge.');
        }
    }

    /** @param array<string, mixed> $row */
    private function classifyExisting(array $row): string
    {
        $statement = $this->connection->prepare('SELECT revision_checksum FROM content_seo.editorial_seo_revision_journal WHERE resource_key=:resource_key AND stream_type=:stream_type AND revision=:revision');
        $statement->execute(['resource_key' => $row['resource_key'], 'stream_type' => $row['stream_type'], 'revision' => $row['revision']]);
        $checksum = $statement->fetchColumn();
        if (! is_string($checksum)) {
            return 'version_conflict';
        }

        return hash_equals($checksum, (string) $row['revision_checksum']) ? 'already_applied' : 'divergent_revision';
    }

    /** @return array<string, mixed>|false */
    private function currentRow(string $resource, string $stream, bool $forUpdate): array|false
    {
        $sql = $this->selectSql().' WHERE resource_key=:resource_key AND stream_type=:stream_type ORDER BY revision DESC LIMIT 1'.($forUpdate ? ' FOR UPDATE' : '');
        $statement = $this->connection->prepare($sql);
        $statement->execute(['resource_key' => $resource, 'stream_type' => $stream]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    private function selectSql(): string
    {
        return 'SELECT resource_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum FROM content_seo.editorial_seo_revision_journal';
    }
}

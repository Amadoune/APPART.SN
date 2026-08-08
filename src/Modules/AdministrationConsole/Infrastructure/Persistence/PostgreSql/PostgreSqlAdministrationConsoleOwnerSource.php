<?php

namespace Appart\Modules\AdministrationConsole\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationAuditReadResult;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationAuditRevisionState;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationAuditWriteResult;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationConsoleOwnerSource;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationOperatorReadResult;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationOperatorRevisionState;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationOperatorWriteResult;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationQueueReadResult;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationQueueRevisionState;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationQueueWriteResult;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationObservedAt;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationSubjectKey;
use Appart\Modules\AdministrationConsole\Infrastructure\Persistence\AdministrationConsoleOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

/** @phpstan-type OwnerRow array{subject_key:string,stream_type:string,revision:int,decision:string,effective_at:string,recorded_at:string,revision_checksum:string} */
final readonly class PostgreSqlAdministrationConsoleOwnerSource implements AdministrationConsoleOwnerSource
{
    private const SAVEPOINT = 'administration_console_owner_source';

    public function __construct(private PDO $connection, private AdministrationConsoleOwnerSourceMapper $mapper) {}

    public function appendOperator(AdministrationOperatorRevisionState $revision): AdministrationOperatorWriteResult
    {
        try {
            return AdministrationOperatorWriteResult::from($this->appendRow($this->mapper->operatorToRow($revision)));
        } catch (PDOException) {
            return AdministrationOperatorWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return AdministrationOperatorWriteResult::Corrupted;
        }
    }

    public function appendQueue(AdministrationQueueRevisionState $revision): AdministrationQueueWriteResult
    {
        try {
            return AdministrationQueueWriteResult::from($this->appendRow($this->mapper->queueToRow($revision)));
        } catch (PDOException) {
            return AdministrationQueueWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return AdministrationQueueWriteResult::Corrupted;
        }
    }

    public function appendAudit(AdministrationAuditRevisionState $revision): AdministrationAuditWriteResult
    {
        try {
            return AdministrationAuditWriteResult::from($this->appendRow($this->mapper->auditToRow($revision)));
        } catch (PDOException) {
            return AdministrationAuditWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return AdministrationAuditWriteResult::Corrupted;
        }
    }

    public function readOperator(AdministrationSubjectKey $subject, AdministrationObservedAt $observedAt): AdministrationOperatorReadResult
    {
        try {
            $row = $this->temporalRow($subject->value, 'operator', $observedAt->canonical());

            return $row === false ? AdministrationOperatorReadResult::missing() : AdministrationOperatorReadResult::found($this->mapper->toOperatorState($row));
        } catch (PDOException) {
            return AdministrationOperatorReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return AdministrationOperatorReadResult::corrupted();
        }
    }

    public function readQueue(AdministrationSubjectKey $subject, AdministrationObservedAt $observedAt): AdministrationQueueReadResult
    {
        try {
            $row = $this->temporalRow($subject->value, 'queue', $observedAt->canonical());

            return $row === false ? AdministrationQueueReadResult::missing() : AdministrationQueueReadResult::found($this->mapper->toQueueState($row));
        } catch (PDOException) {
            return AdministrationQueueReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return AdministrationQueueReadResult::corrupted();
        }
    }

    public function readAudit(AdministrationSubjectKey $subject, AdministrationObservedAt $observedAt): AdministrationAuditReadResult
    {
        try {
            $row = $this->temporalRow($subject->value, 'audit', $observedAt->canonical());

            return $row === false ? AdministrationAuditReadResult::missing() : AdministrationAuditReadResult::found($this->mapper->toAuditState($row));
        } catch (PDOException) {
            return AdministrationAuditReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return AdministrationAuditReadResult::corrupted();
        }
    }

    /** @param OwnerRow $row */
    private function appendRow(array $row): string
    {
        return $this->transactional(function () use ($row): string {
            $this->lockStream($row['subject_key'], $row['stream_type']);
            $current = $this->currentRow($row['subject_key'], $row['stream_type']);
            if ($current === false) {
                if ($row['revision'] !== 1) {
                    return 'version_conflict';
                }
            } else {
                $currentRevision = $current['revision'];
                if ($row['revision'] <= $currentRevision) {
                    return $this->classifyExisting($row);
                }
                if ($row['revision'] !== $currentRevision + 1 || new DateTimeImmutable($row['effective_at']) <= new DateTimeImmutable($current['effective_at']) || new DateTimeImmutable($row['recorded_at']) < new DateTimeImmutable($current['recorded_at'])) {
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
    private function temporalRow(string $key, string $stream, string $at): array|false
    {
        $query = $this->connection->prepare($this->selectSql().' WHERE subject_key=:key AND stream_type=:stream AND effective_at<=CAST(:at AS timestamptz) AND recorded_at<=CAST(:at AS timestamptz) ORDER BY revision DESC LIMIT 1');
        $query->execute(['key' => $key, 'stream' => $stream, 'at' => $at]);

        /** @var OwnerRow|false */ return $query->fetch(PDO::FETCH_ASSOC);
    }

    private function transactional(callable $operation): string
    {
        $owner = ! $this->connection->inTransaction();
        $owner ? $this->connection->beginTransaction() : $this->connection->exec('SAVEPOINT '.self::SAVEPOINT);
        try {
            $result = $operation();
            $owner ? $this->connection->commit() : $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);

            return $result;
        } catch (Throwable $exception) {
            if ($owner) {
                $this->connection->rollBack();
            } else {
                $this->connection->exec('ROLLBACK TO SAVEPOINT '.self::SAVEPOINT);
                $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);
            } throw $exception;
        }
    }

    private function lockStream(string $key, string $stream): void
    {
        $query = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:key,0))');
        $query->execute(['key' => $key."\n".$stream]);
    }

    /** @param OwnerRow $row */
    private function insertOrClassify(array $row): string
    {
        $query = $this->connection->prepare('INSERT INTO administration_console.owner_revision_journal(subject_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum) VALUES(:subject_key,:stream_type,:revision,:decision,CAST(:effective_at AS timestamptz),CAST(:recorded_at AS timestamptz),:revision_checksum) ON CONFLICT(subject_key,stream_type,revision) DO NOTHING');
        $query->execute($row);

        return $query->rowCount() === 1 ? 'applied' : $this->classifyExisting($row);
    }

    /** @param OwnerRow $row */
    private function updateIndex(array $row): void
    {
        $query = $this->connection->prepare('INSERT INTO administration_console.owner_current_index(subject_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum) VALUES(:subject_key,:stream_type,:revision,:decision,CAST(:effective_at AS timestamptz),CAST(:recorded_at AS timestamptz),:revision_checksum) ON CONFLICT(subject_key,stream_type) DO UPDATE SET revision=EXCLUDED.revision,decision=EXCLUDED.decision,effective_at=EXCLUDED.effective_at,recorded_at=EXCLUDED.recorded_at,revision_checksum=EXCLUDED.revision_checksum WHERE EXCLUDED.revision>owner_current_index.revision');
        $query->execute($row);
        if ($query->rowCount() !== 1) {
            throw new RuntimeException('AdministrationConsole current index did not converge.');
        }
    }

    /** @param OwnerRow $row */
    private function classifyExisting(array $row): string
    {
        $query = $this->connection->prepare('SELECT revision_checksum FROM administration_console.owner_revision_journal WHERE subject_key=:subject_key AND stream_type=:stream_type AND revision=:revision');
        $query->execute(['subject_key' => $row['subject_key'], 'stream_type' => $row['stream_type'], 'revision' => $row['revision']]);
        $checksum = $query->fetchColumn();
        if (! is_string($checksum)) {
            return 'version_conflict';
        }

        return hash_equals($checksum, $row['revision_checksum']) ? 'already_applied' : 'divergent_revision';
    }

    /** @return OwnerRow|false */
    private function currentRow(string $key, string $stream): array|false
    {
        $query = $this->connection->prepare($this->selectSql().' WHERE subject_key=:key AND stream_type=:stream ORDER BY revision DESC LIMIT 1 FOR UPDATE');
        $query->execute(['key' => $key, 'stream' => $stream]);

        /** @var OwnerRow|false */ return $query->fetch(PDO::FETCH_ASSOC);
    }

    private function selectSql(): string
    {
        return 'SELECT subject_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum FROM administration_console.owner_revision_journal';
    }
}

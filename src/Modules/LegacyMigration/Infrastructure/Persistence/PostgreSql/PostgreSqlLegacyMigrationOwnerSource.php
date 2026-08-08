<?php

namespace Appart\Modules\LegacyMigration\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationCutoverReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationCutoverRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationCutoverWriteResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationInventoryReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationInventoryRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationInventoryWriteResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationOwnerSource;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationQuarantineReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationQuarantineRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationQuarantineWriteResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationReconciliationReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationReconciliationRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationReconciliationWriteResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationWaveReadResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationWaveRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationWaveWriteResult;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;
use Appart\Modules\LegacyMigration\Infrastructure\Persistence\LegacyMigrationOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

/** @phpstan-type OwnerRow array{subject_key:string,stream_type:string,revision:int,decision:string,effective_at:string,recorded_at:string,revision_checksum:string} */
final readonly class PostgreSqlLegacyMigrationOwnerSource implements LegacyMigrationOwnerSource
{
    private const SAVEPOINT = 'legacy_migration_owner_source';

    public function __construct(private PDO $connection, private LegacyMigrationOwnerSourceMapper $mapper) {}

    public function appendInventory(LegacyMigrationInventoryRevisionState $revision): LegacyMigrationInventoryWriteResult
    {
        try {
            return LegacyMigrationInventoryWriteResult::from($this->appendRow($this->mapper->inventoryToRow($revision)));
        } catch (PDOException) {
            return LegacyMigrationInventoryWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return LegacyMigrationInventoryWriteResult::Corrupted;
        }
    }

    public function appendWave(LegacyMigrationWaveRevisionState $revision): LegacyMigrationWaveWriteResult
    {
        try {
            return LegacyMigrationWaveWriteResult::from($this->appendRow($this->mapper->waveToRow($revision)));
        } catch (PDOException) {
            return LegacyMigrationWaveWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return LegacyMigrationWaveWriteResult::Corrupted;
        }
    }

    public function appendReconciliation(LegacyMigrationReconciliationRevisionState $revision): LegacyMigrationReconciliationWriteResult
    {
        try {
            return LegacyMigrationReconciliationWriteResult::from($this->appendRow($this->mapper->reconciliationToRow($revision)));
        } catch (PDOException) {
            return LegacyMigrationReconciliationWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return LegacyMigrationReconciliationWriteResult::Corrupted;
        }
    }

    public function appendQuarantine(LegacyMigrationQuarantineRevisionState $revision): LegacyMigrationQuarantineWriteResult
    {
        try {
            return LegacyMigrationQuarantineWriteResult::from($this->appendRow($this->mapper->quarantineToRow($revision)));
        } catch (PDOException) {
            return LegacyMigrationQuarantineWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return LegacyMigrationQuarantineWriteResult::Corrupted;
        }
    }

    public function appendCutover(LegacyMigrationCutoverRevisionState $revision): LegacyMigrationCutoverWriteResult
    {
        try {
            return LegacyMigrationCutoverWriteResult::from($this->appendRow($this->mapper->cutoverToRow($revision)));
        } catch (PDOException) {
            return LegacyMigrationCutoverWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return LegacyMigrationCutoverWriteResult::Corrupted;
        }
    }

    public function readInventory(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationInventoryReadResult
    {
        try {
            $r = $this->temporalRow($subject->value, 'inventory', $observedAt->canonical());

            return $r === false ? LegacyMigrationInventoryReadResult::missing() : LegacyMigrationInventoryReadResult::found($this->mapper->toInventoryState($r));
        } catch (PDOException) {
            return LegacyMigrationInventoryReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return LegacyMigrationInventoryReadResult::corrupted();
        }
    }

    public function readWave(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationWaveReadResult
    {
        try {
            $r = $this->temporalRow($subject->value, 'wave', $observedAt->canonical());

            return $r === false ? LegacyMigrationWaveReadResult::missing() : LegacyMigrationWaveReadResult::found($this->mapper->toWaveState($r));
        } catch (PDOException) {
            return LegacyMigrationWaveReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return LegacyMigrationWaveReadResult::corrupted();
        }
    }

    public function readReconciliation(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationReconciliationReadResult
    {
        try {
            $r = $this->temporalRow($subject->value, 'reconciliation', $observedAt->canonical());

            return $r === false ? LegacyMigrationReconciliationReadResult::missing() : LegacyMigrationReconciliationReadResult::found($this->mapper->toReconciliationState($r));
        } catch (PDOException) {
            return LegacyMigrationReconciliationReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return LegacyMigrationReconciliationReadResult::corrupted();
        }
    }

    public function readQuarantine(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationQuarantineReadResult
    {
        try {
            $r = $this->temporalRow($subject->value, 'quarantine', $observedAt->canonical());

            return $r === false ? LegacyMigrationQuarantineReadResult::missing() : LegacyMigrationQuarantineReadResult::found($this->mapper->toQuarantineState($r));
        } catch (PDOException) {
            return LegacyMigrationQuarantineReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return LegacyMigrationQuarantineReadResult::corrupted();
        }
    }

    public function readCutover(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationCutoverReadResult
    {
        try {
            $r = $this->temporalRow($subject->value, 'cutover', $observedAt->canonical());

            return $r === false ? LegacyMigrationCutoverReadResult::missing() : LegacyMigrationCutoverReadResult::found($this->mapper->toCutoverState($r));
        } catch (PDOException) {
            return LegacyMigrationCutoverReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return LegacyMigrationCutoverReadResult::corrupted();
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
                $cr = $current['revision'];
                if ($row['revision'] <= $cr) {
                    return $this->classifyExisting($row);
                }if ($row['revision'] !== $cr + 1 || new DateTimeImmutable($row['effective_at']) <= new DateTimeImmutable($current['effective_at']) || new DateTimeImmutable($row['recorded_at']) < new DateTimeImmutable($current['recorded_at'])) {
                    return 'version_conflict';
                }
            }$result = $this->insertOrClassify($row);
            if ($result === 'applied') {
                $this->updateIndex($row);
            }

            return $result;
        });
    }

    /** @return OwnerRow|false */
    private function temporalRow(string $key, string $stream, string $at): array|false
    {
        $q = $this->connection->prepare($this->selectSql().' WHERE subject_key=:key AND stream_type=:stream AND effective_at<=CAST(:at AS timestamptz) AND recorded_at<=CAST(:at AS timestamptz) ORDER BY revision DESC LIMIT 1');
        $q->execute(['key' => $key, 'stream' => $stream, 'at' => $at]); /** @var OwnerRow|false */

        return $q->fetch(PDO::FETCH_ASSOC);
    }

    private function transactional(callable $operation): string
    {
        $owner = ! $this->connection->inTransaction();
        $owner ? $this->connection->beginTransaction() : $this->connection->exec('SAVEPOINT '.self::SAVEPOINT);
        try {
            $result = $operation();
            $owner ? $this->connection->commit() : $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);

            return $result;
        } catch (Throwable $e) {
            if ($owner) {
                $this->connection->rollBack();
            } else {
                $this->connection->exec('ROLLBACK TO SAVEPOINT '.self::SAVEPOINT);
                $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);
            }throw $e;
        }
    }

    private function lockStream(string $key, string $stream): void
    {
        $q = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:key,0))');
        $q->execute(['key' => $key."\n".$stream]);
    }

    /** @param OwnerRow $row */
    private function insertOrClassify(array $row): string
    {
        $q = $this->connection->prepare('INSERT INTO legacy_migration.owner_revision_journal(subject_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum) VALUES(:subject_key,:stream_type,:revision,:decision,CAST(:effective_at AS timestamptz),CAST(:recorded_at AS timestamptz),:revision_checksum) ON CONFLICT(subject_key,stream_type,revision) DO NOTHING');
        $q->execute($row);

        return $q->rowCount() === 1 ? 'applied' : $this->classifyExisting($row);
    }

    /** @param OwnerRow $row */
    private function updateIndex(array $row): void
    {
        $q = $this->connection->prepare('INSERT INTO legacy_migration.owner_current_index(subject_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum) VALUES(:subject_key,:stream_type,:revision,:decision,CAST(:effective_at AS timestamptz),CAST(:recorded_at AS timestamptz),:revision_checksum) ON CONFLICT(subject_key,stream_type) DO UPDATE SET revision=EXCLUDED.revision,decision=EXCLUDED.decision,effective_at=EXCLUDED.effective_at,recorded_at=EXCLUDED.recorded_at,revision_checksum=EXCLUDED.revision_checksum WHERE EXCLUDED.revision>owner_current_index.revision');
        $q->execute($row);
        if ($q->rowCount() !== 1) {
            throw new RuntimeException('LegacyMigration current index did not converge.');
        }
    }

    /** @param OwnerRow $row */
    private function classifyExisting(array $row): string
    {
        $q = $this->connection->prepare('SELECT revision_checksum FROM legacy_migration.owner_revision_journal WHERE subject_key=:subject_key AND stream_type=:stream_type AND revision=:revision');
        $q->execute(['subject_key' => $row['subject_key'], 'stream_type' => $row['stream_type'], 'revision' => $row['revision']]);
        $checksum = $q->fetchColumn();
        if (! is_string($checksum)) {
            return 'version_conflict';
        }

        return hash_equals($checksum, $row['revision_checksum']) ? 'already_applied' : 'divergent_revision';
    }

    /** @return OwnerRow|false */
    private function currentRow(string $key, string $stream): array|false
    {
        $q = $this->connection->prepare($this->selectSql().' WHERE subject_key=:key AND stream_type=:stream ORDER BY revision DESC LIMIT 1 FOR UPDATE');
        $q->execute(['key' => $key, 'stream' => $stream]); /** @var OwnerRow|false */

        return $q->fetch(PDO::FETCH_ASSOC);
    }

    private function selectSql(): string
    {
        return 'SELECT subject_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum FROM legacy_migration.owner_revision_journal';
    }
}

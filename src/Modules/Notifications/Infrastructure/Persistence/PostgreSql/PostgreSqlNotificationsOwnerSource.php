<?php

namespace Appart\Modules\Notifications\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Notifications\Application\OwnerSource\NotificationChannelReadResult;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationChannelRevisionState;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationChannelWriteResult;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationPreferenceReadResult;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationPreferenceRevisionState;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationPreferenceWriteResult;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationsOwnerSource;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationTemplateReadResult;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationTemplateRevisionState;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationTemplateWriteResult;
use Appart\Modules\Notifications\Application\PublicRead\NotificationObservedAt;
use Appart\Modules\Notifications\Application\PublicRead\NotificationSubjectKey;
use Appart\Modules\Notifications\Infrastructure\Persistence\NotificationsOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

/** @phpstan-type OwnerRow array{subject_key:string,stream_type:string,revision:int,decision:string,effective_at:string,recorded_at:string,revision_checksum:string} */
final readonly class PostgreSqlNotificationsOwnerSource implements NotificationsOwnerSource
{
    private const SAVEPOINT = 'notifications_owner_source';

    public function __construct(private PDO $connection, private NotificationsOwnerSourceMapper $mapper) {}

    public function appendPreference(NotificationPreferenceRevisionState $r): NotificationPreferenceWriteResult
    {
        try {
            return NotificationPreferenceWriteResult::from($this->appendRow($this->mapper->preferenceToRow($r)));
        } catch (PDOException) {
            return NotificationPreferenceWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return NotificationPreferenceWriteResult::Corrupted;
        }
    }

    public function appendTemplate(NotificationTemplateRevisionState $r): NotificationTemplateWriteResult
    {
        try {
            return NotificationTemplateWriteResult::from($this->appendRow($this->mapper->templateToRow($r)));
        } catch (PDOException) {
            return NotificationTemplateWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return NotificationTemplateWriteResult::Corrupted;
        }
    }

    public function appendChannel(NotificationChannelRevisionState $r): NotificationChannelWriteResult
    {
        try {
            return NotificationChannelWriteResult::from($this->appendRow($this->mapper->channelToRow($r)));
        } catch (PDOException) {
            return NotificationChannelWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return NotificationChannelWriteResult::Corrupted;
        }
    }

    public function readPreference(NotificationSubjectKey $s, NotificationObservedAt $o): NotificationPreferenceReadResult
    {
        try {
            $r = $this->temporalRow($s->value, 'preference', $o->canonical());

            return $r === false ? NotificationPreferenceReadResult::missing() : NotificationPreferenceReadResult::found($this->mapper->toPreferenceState($r));
        } catch (PDOException) {
            return NotificationPreferenceReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return NotificationPreferenceReadResult::corrupted();
        }
    }

    public function readTemplate(NotificationSubjectKey $s, NotificationObservedAt $o): NotificationTemplateReadResult
    {
        try {
            $r = $this->temporalRow($s->value, 'template', $o->canonical());

            return $r === false ? NotificationTemplateReadResult::missing() : NotificationTemplateReadResult::found($this->mapper->toTemplateState($r));
        } catch (PDOException) {
            return NotificationTemplateReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return NotificationTemplateReadResult::corrupted();
        }
    }

    public function readChannel(NotificationSubjectKey $s, NotificationObservedAt $o): NotificationChannelReadResult
    {
        try {
            $r = $this->temporalRow($s->value, 'channel', $o->canonical());

            return $r === false ? NotificationChannelReadResult::missing() : NotificationChannelReadResult::found($this->mapper->toChannelState($r));
        } catch (PDOException) {
            return NotificationChannelReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return NotificationChannelReadResult::corrupted();
        }
    }

    /** @param OwnerRow $row */
    private function appendRow(array $row): string
    {
        return $this->transactional(function () use ($row): string {
            $this->lockStream((string) $row['subject_key'], (string) $row['stream_type']);
            $current = $this->currentRow((string) $row['subject_key'], (string) $row['stream_type']);
            if ($current === false) {
                if ((int) $row['revision'] !== 1) {
                    return 'version_conflict';
                }
            } else {
                $rev = (int) $current['revision'];
                if ((int) $row['revision'] <= $rev) {
                    return $this->classifyExisting($row);
                }
                if ((int) $row['revision'] !== $rev + 1 || new DateTimeImmutable((string) $row['effective_at']) <= new DateTimeImmutable((string) $current['effective_at']) || new DateTimeImmutable((string) $row['recorded_at']) < new DateTimeImmutable((string) $current['recorded_at'])) {
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
        $q = $this->connection->prepare($this->selectSql().' WHERE subject_key=:key AND stream_type=:stream AND effective_at<=CAST(:at AS timestamptz) AND recorded_at<=CAST(:at AS timestamptz) ORDER BY revision DESC LIMIT 1');
        $q->execute(['key' => $key, 'stream' => $stream, 'at' => $at]);

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
            }
            throw $e;
        }
    }

    private function lockStream(string $key, string $stream): void
    {
        $q = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:key,0))');
        $q->execute(['key' => $key."\n".$stream]);
    }

    /** @param OwnerRow $r */
    private function insertOrClassify(array $r): string
    {
        $q = $this->connection->prepare('INSERT INTO notifications.owner_revision_journal(subject_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum) VALUES(:subject_key,:stream_type,:revision,:decision,CAST(:effective_at AS timestamptz),CAST(:recorded_at AS timestamptz),:revision_checksum) ON CONFLICT(subject_key,stream_type,revision) DO NOTHING');
        $q->execute($r);

        return $q->rowCount() === 1 ? 'applied' : $this->classifyExisting($r);
    }

    /** @param OwnerRow $r */
    private function updateIndex(array $r): void
    {
        $q = $this->connection->prepare('INSERT INTO notifications.owner_current_index(subject_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum) VALUES(:subject_key,:stream_type,:revision,:decision,CAST(:effective_at AS timestamptz),CAST(:recorded_at AS timestamptz),:revision_checksum) ON CONFLICT(subject_key,stream_type) DO UPDATE SET revision=EXCLUDED.revision,decision=EXCLUDED.decision,effective_at=EXCLUDED.effective_at,recorded_at=EXCLUDED.recorded_at,revision_checksum=EXCLUDED.revision_checksum WHERE EXCLUDED.revision>owner_current_index.revision');
        $q->execute($r);
        if ($q->rowCount() !== 1) {
            throw new RuntimeException('Notifications current index did not converge.');
        }
    }

    /** @param OwnerRow $r */
    private function classifyExisting(array $r): string
    {
        $q = $this->connection->prepare('SELECT revision_checksum FROM notifications.owner_revision_journal WHERE subject_key=:subject_key AND stream_type=:stream_type AND revision=:revision');
        $q->execute(['subject_key' => $r['subject_key'], 'stream_type' => $r['stream_type'], 'revision' => $r['revision']]);
        $checksum = $q->fetchColumn();
        if (! is_string($checksum)) {
            return 'version_conflict';
        }

        return hash_equals($checksum, (string) $r['revision_checksum']) ? 'already_applied' : 'divergent_revision';
    }

    /** @return OwnerRow|false */
    private function currentRow(string $key, string $stream): array|false
    {
        $q = $this->connection->prepare($this->selectSql().' WHERE subject_key=:key AND stream_type=:stream ORDER BY revision DESC LIMIT 1 FOR UPDATE');
        $q->execute(['key' => $key, 'stream' => $stream]);

        return $q->fetch(PDO::FETCH_ASSOC);
    }

    private function selectSql(): string
    {
        return 'SELECT subject_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum FROM notifications.owner_revision_journal';
    }
}

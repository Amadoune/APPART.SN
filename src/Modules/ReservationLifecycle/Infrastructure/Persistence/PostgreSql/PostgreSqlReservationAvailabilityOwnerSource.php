<?php

namespace Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\Contract\ReservationAvailabilityOwnerSource;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityReadResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityRevisionDecision;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityRevisionState;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityWriteResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityIntentId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityObservedAt;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilitySubjectId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityWindow;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\ReservationAvailabilityOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final readonly class PostgreSqlReservationAvailabilityOwnerSource implements ReservationAvailabilityOwnerSource
{
    private const SAVEPOINT = 'reservation_availability_source';

    public function __construct(
        private PDO $connection,
        private ReservationAvailabilityOwnerSourceMapper $mapper,
    ) {}

    public function append(ReservationAvailabilityRevisionState $revision): ReservationAvailabilityWriteResult
    {
        try {
            return $this->transactional(function () use ($revision): ReservationAvailabilityWriteResult {
                $this->lockSubject($revision->subjectId);
                $row = $this->mapper->toRow($revision);
                $current = $this->currentRow($revision->intentId, true);
                if ($current === false) {
                    if ($revision->revision !== 1 || $revision->decision !== ReservationAvailabilityRevisionDecision::Proposed) {
                        return ReservationAvailabilityWriteResult::VersionConflict;
                    }

                    return $this->insertOrClassify($row);
                }

                $currentVersion = (int) $current['revision'];
                if ($revision->revision <= $currentVersion) {
                    return $this->classifyExisting($row);
                }
                if (! $this->continues($revision, $current)) {
                    return ReservationAvailabilityWriteResult::VersionConflict;
                }
                if ($revision->decision->blocksAvailability()
                    && $this->conflictExists($revision->intentId, $revision->subjectId, $revision->window, $revision->recordedAt)) {
                    return ReservationAvailabilityWriteResult::AvailabilityConflict;
                }

                return $this->insertOrClassify($row);
            });
        } catch (PDOException) {
            return ReservationAvailabilityWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return ReservationAvailabilityWriteResult::Corrupted;
        }
    }

    public function read(
        ReservationAvailabilityIntentId $intentId,
        ReservationAvailabilitySubjectId $subjectId,
        ReservationAvailabilityWindow $window,
        ReservationAvailabilityObservedAt $observedAt,
    ): ReservationAvailabilityReadResult {
        try {
            $history = $this->history($intentId);
            $current = $this->applicable($history, $observedAt->value);
            if ($current === null) {
                return ReservationAvailabilityReadResult::missing($intentId);
            }
            if ($current->subjectId->value !== $subjectId->value
                || $current->window->canonical() !== $window->canonical()) {
                return ReservationAvailabilityReadResult::corrupted($intentId);
            }

            return $this->conflictExists($intentId, $subjectId, $window, $observedAt->value)
                ? ReservationAvailabilityReadResult::conflicting($current)
                : ReservationAvailabilityReadResult::available($current);
        } catch (PDOException) {
            return ReservationAvailabilityReadResult::dependencyUnavailable($intentId);
        } catch (Throwable) {
            return ReservationAvailabilityReadResult::corrupted($intentId);
        }
    }

    public function history(ReservationAvailabilityIntentId $intentId): array
    {
        $statement = $this->connection->prepare($this->selectSql().' WHERE availability_intent_id=:intent_id ORDER BY revision ASC');
        $statement->execute(['intent_id' => $intentId->value]);

        return $this->validatedHistory($statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /** @param callable(): ReservationAvailabilityWriteResult $operation */
    private function transactional(callable $operation): ReservationAvailabilityWriteResult
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        } else {
            $this->connection->exec('SAVEPOINT '.self::SAVEPOINT);
        }
        try {
            $result = $operation();
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            } else {
                $this->connection->exec('ROLLBACK TO SAVEPOINT '.self::SAVEPOINT);
                $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);
            }
            throw $error;
        }
        if ($owner) {
            $this->connection->commit();
        } else {
            $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);
        }

        return $result;
    }

    private function lockSubject(ReservationAvailabilitySubjectId $subjectId): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:subject_id,0))');
        $statement->execute(['subject_id' => $subjectId->value]);
    }

    /** @param array<string, mixed> $current */
    private function continues(ReservationAvailabilityRevisionState $revision, array $current): bool
    {
        if ($revision->revision !== (int) $current['revision'] + 1
            || $revision->subjectId->value !== (string) $current['availability_subject_id']
            || $revision->window->canonical() !== $this->windowFromRow($current)->canonical()
            || $revision->effectiveAt <= new DateTimeImmutable((string) $current['effective_at'])
            || $revision->recordedAt < new DateTimeImmutable((string) $current['recorded_at'])) {
            return false;
        }

        $previous = ReservationAvailabilityRevisionDecision::from((string) $current['decision']);

        return match ($previous) {
            ReservationAvailabilityRevisionDecision::Proposed => $revision->decision === ReservationAvailabilityRevisionDecision::Held,
            ReservationAvailabilityRevisionDecision::Held => in_array($revision->decision, [ReservationAvailabilityRevisionDecision::Committed, ReservationAvailabilityRevisionDecision::Released, ReservationAvailabilityRevisionDecision::Expired], true),
            ReservationAvailabilityRevisionDecision::Committed => $revision->decision === ReservationAvailabilityRevisionDecision::Released,
            ReservationAvailabilityRevisionDecision::Released,
            ReservationAvailabilityRevisionDecision::Expired => false,
        };
    }

    private function conflictExists(
        ReservationAvailabilityIntentId $excludedIntentId,
        ReservationAvailabilitySubjectId $subjectId,
        ReservationAvailabilityWindow $window,
        DateTimeImmutable $observedAt,
    ): bool {
        $statement = $this->connection->prepare(
            $this->selectSql().' WHERE availability_subject_id=:subject_id
             AND window_start < CAST(:window_end AS timestamptz)
             AND window_end > CAST(:window_start AS timestamptz)
             ORDER BY availability_intent_id,revision ASC',
        );
        $statement->execute([
            'subject_id' => $subjectId->value,
            'window_start' => $window->startsAt->format('Y-m-d H:i:s.uP'),
            'window_end' => $window->endsAt->format('Y-m-d H:i:s.uP'),
        ]);
        $grouped = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $grouped[(string) $row['availability_intent_id']][] = $row;
        }
        foreach ($grouped as $intent => $rows) {
            if ($intent === $excludedIntentId->value) {
                continue;
            }
            $current = $this->applicable($this->validatedHistory($rows), $observedAt);
            if ($current?->decision->blocksAvailability() === true) {
                return true;
            }
        }

        return false;
    }

    /** @param list<ReservationAvailabilityRevisionState> $history */
    private function applicable(array $history, DateTimeImmutable $observedAt): ?ReservationAvailabilityRevisionState
    {
        $applicable = null;
        foreach ($history as $revision) {
            if ($revision->effectiveAt <= $observedAt && $revision->recordedAt <= $observedAt) {
                $applicable = $revision;
            }
        }

        return $applicable;
    }

    /** @param list<array<string, mixed>> $rows
     * @return list<ReservationAvailabilityRevisionState>
     */
    private function validatedHistory(array $rows): array
    {
        $states = [];
        $expectedRevision = 1;
        $previous = null;
        foreach ($rows as $row) {
            $state = $this->mapper->toState($row);
            if ($state->revision !== $expectedRevision
                || ($previous !== null && ($state->subjectId->value !== $previous->subjectId->value
                    || $state->window->canonical() !== $previous->window->canonical()
                    || $state->effectiveAt <= $previous->effectiveAt
                    || $state->recordedAt < $previous->recordedAt))) {
                throw new RuntimeException('Corrupted reservation availability revision chronology.');
            }
            $states[] = $state;
            $previous = $state;
            $expectedRevision++;
        }

        return $states;
    }

    /** @param array{intent_id:string,revision:int,subject_id:string,window_start:string,window_end:string,decision:string,effective_at:string,recorded_at:string,checksum:string} $row */
    private function insertOrClassify(array $row): ReservationAvailabilityWriteResult
    {
        $statement = $this->connection->prepare(
            'INSERT INTO reservation_lifecycle.availability_intent_revisions
             (availability_intent_id,revision,availability_subject_id,window_start,window_end,decision,effective_at,recorded_at,revision_checksum)
             VALUES(CAST(:intent_id AS uuid),:revision,CAST(:subject_id AS uuid),CAST(:window_start AS timestamptz),CAST(:window_end AS timestamptz),:decision,CAST(:effective_at AS timestamptz),CAST(:recorded_at AS timestamptz),:checksum)
             ON CONFLICT (availability_intent_id,revision) DO NOTHING',
        );
        $statement->execute($row);

        return $statement->rowCount() === 1
            ? ReservationAvailabilityWriteResult::Applied
            : $this->classifyExisting($row);
    }

    /** @param array{intent_id:string,revision:int,subject_id:string,window_start:string,window_end:string,decision:string,effective_at:string,recorded_at:string,checksum:string} $row */
    private function classifyExisting(array $row): ReservationAvailabilityWriteResult
    {
        $statement = $this->connection->prepare(
            'SELECT revision_checksum FROM reservation_lifecycle.availability_intent_revisions
             WHERE availability_intent_id=:intent_id AND revision=:revision',
        );
        $statement->execute(['intent_id' => $row['intent_id'], 'revision' => $row['revision']]);
        $checksum = $statement->fetchColumn();
        if (! is_string($checksum)) {
            return ReservationAvailabilityWriteResult::Corrupted;
        }

        return hash_equals($checksum, $row['checksum'])
            ? ReservationAvailabilityWriteResult::AlreadyApplied
            : ReservationAvailabilityWriteResult::DivergentRevision;
    }

    /** @return array<string, mixed>|false */
    private function currentRow(ReservationAvailabilityIntentId $intentId, bool $forUpdate): array|false
    {
        $sql = $this->selectSql().' WHERE availability_intent_id=:intent_id ORDER BY revision DESC LIMIT 1';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }
        $statement = $this->connection->prepare($sql);
        $statement->execute(['intent_id' => $intentId->value]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /** @param array<string, mixed> $row */
    private function windowFromRow(array $row): ReservationAvailabilityWindow
    {
        return new ReservationAvailabilityWindow(
            new DateTimeImmutable((string) $row['window_start']),
            new DateTimeImmutable((string) $row['window_end']),
        );
    }

    private function selectSql(): string
    {
        return 'SELECT availability_intent_id::text,revision,availability_subject_id::text,window_start,window_end,decision,effective_at,recorded_at,revision_checksum
                FROM reservation_lifecycle.availability_intent_revisions';
    }
}

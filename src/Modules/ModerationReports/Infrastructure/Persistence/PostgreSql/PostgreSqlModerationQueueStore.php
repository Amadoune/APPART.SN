<?php

namespace Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationQueueStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceWriteResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationQueueClaimResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationQueueItemState;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\ModerationPersistenceMapper;
use DateTimeImmutable;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlModerationQueueStore implements ModerationQueueStore
{
    public function __construct(
        private PDO $connection,
        private ModerationPersistenceMapper $mapper,
    ) {}

    public function project(ModerationQueueItemState $item): ModerationPersistenceWriteResult
    {
        try {
            return $this->transaction(function () use ($item): ModerationPersistenceWriteResult {
                $this->lock($item->queueItemId);
                $current = $this->read($item->queueItemId);
                if ($current !== null && $current->sourceVersion > $item->sourceVersion) {
                    return ModerationPersistenceWriteResult::VersionConflict;
                }
                if ($current !== null && $current->sourceVersion === $item->sourceVersion) {
                    return $current == $item
                        ? ModerationPersistenceWriteResult::AlreadyApplied
                        : ModerationPersistenceWriteResult::DivergentIntent;
                }
                $statement = $this->connection->prepare(
                    'INSERT INTO moderation_reports.queue_items(queue_item_id,case_id,priority,category,state,lease_id,claim_owner_id,lease_expires_at,source_version,updated_at)
                     VALUES(CAST(:queue_item_id AS uuid),CAST(:case_id AS uuid),:priority,:category,:state,CAST(:lease_id AS uuid),CAST(:claim_owner_id AS uuid),CAST(:lease_expires_at AS timestamptz),:source_version,CAST(:updated_at AS timestamptz))
                     ON CONFLICT(queue_item_id) DO UPDATE SET priority=EXCLUDED.priority,category=EXCLUDED.category,state=EXCLUDED.state,
                        lease_id=EXCLUDED.lease_id,claim_owner_id=EXCLUDED.claim_owner_id,lease_expires_at=EXCLUDED.lease_expires_at,
                        source_version=EXCLUDED.source_version,updated_at=EXCLUDED.updated_at',
                );
                $statement->execute($this->mapper->queueParameters($item));

                return ModerationPersistenceWriteResult::Applied;
            });
        } catch (PDOException $error) {
            return $error->getCode() === '23505'
                ? ModerationPersistenceWriteResult::IdentityConflict
                : ModerationPersistenceWriteResult::Rejected;
        }
    }

    public function claim(
        string $queueItemId,
        string $leaseId,
        string $claimOwnerId,
        DateTimeImmutable $leaseExpiresAt,
        DateTimeImmutable $claimedAt,
        ?string $intentId = null,
        ?string $intentChecksum = null,
    ): ModerationQueueClaimResult {
        try {
            return $this->transaction(function () use ($queueItemId, $leaseId, $claimOwnerId, $leaseExpiresAt, $claimedAt, $intentId, $intentChecksum): ModerationQueueClaimResult {
                $this->lock($queueItemId);
                $amended = $intentId !== null || $intentChecksum !== null;
                if ($amended && ($intentId === null || $intentChecksum === null)) {
                    return ModerationQueueClaimResult::Rejected;
                }
                if ($intentId !== null && $intentChecksum !== null) {
                    $intent = $this->claimIntent($queueItemId, $intentId, $intentChecksum);
                    if ($intent !== null) {
                        return $intent;
                    }
                }
                $item = $this->read($queueItemId);
                if ($item === null) {
                    return ModerationQueueClaimResult::Missing;
                }
                if (! $amended && $item->state === 'Claimed' && $item->leaseId === $leaseId && $item->claimOwnerId === $claimOwnerId) {
                    return ModerationQueueClaimResult::AlreadyClaimed;
                }
                if ($item->state === 'Completed' || ($item->state === 'Claimed' && $item->leaseExpiresAt >= $claimedAt)) {
                    return ModerationQueueClaimResult::LeaseConflict;
                }
                $statement = $this->connection->prepare(
                    "UPDATE moderation_reports.queue_items
                     SET state='Claimed',lease_id=CAST(:lease_id AS uuid),claim_owner_id=CAST(:claim_owner_id AS uuid),
                         lease_expires_at=CAST(:lease_expires_at AS timestamptz),updated_at=CAST(:claimed_at AS timestamptz)
                     WHERE queue_item_id=CAST(:queue_item_id AS uuid)",
                );
                $statement->execute([
                    'queue_item_id' => $queueItemId,
                    'lease_id' => $leaseId,
                    'claim_owner_id' => $claimOwnerId,
                    'lease_expires_at' => $leaseExpiresAt->format('Y-m-d H:i:s.uP'),
                    'claimed_at' => $claimedAt->format('Y-m-d H:i:s.uP'),
                ]);

                if ($intentId !== null && $intentChecksum !== null) {
                    $this->appendClaimIntent($queueItemId, $intentId, $intentChecksum, $claimedAt);

                    return ModerationQueueClaimResult::Applied;
                }

                return ModerationQueueClaimResult::Claimed;
            });
        } catch (PDOException) {
            return ModerationQueueClaimResult::Rejected;
        }
    }

    private function claimIntent(string $queueItemId, string $intentId, string $checksum): ?ModerationQueueClaimResult
    {
        $statement = $this->connection->prepare(
            'SELECT intent_checksum FROM moderation_reports.queue_claim_intents
             WHERE queue_item_id=CAST(:queue_item_id AS uuid) AND intent_id=CAST(:intent_id AS uuid)',
        );
        $statement->execute(['queue_item_id' => $queueItemId, 'intent_id' => $intentId]);
        $stored = $statement->fetchColumn();
        if ($stored === false) {
            return null;
        }

        return hash_equals((string) $stored, $checksum)
            ? ModerationQueueClaimResult::AlreadyApplied
            : ModerationQueueClaimResult::DivergentIntent;
    }

    private function appendClaimIntent(
        string $queueItemId,
        string $intentId,
        string $checksum,
        DateTimeImmutable $recordedAt,
    ): void {
        $statement = $this->connection->prepare(
            'INSERT INTO moderation_reports.queue_claim_intents(queue_item_id,intent_id,intent_checksum,recorded_at)
             VALUES(CAST(:queue_item_id AS uuid),CAST(:intent_id AS uuid),:intent_checksum,CAST(:recorded_at AS timestamptz))',
        );
        $statement->execute([
            'queue_item_id' => $queueItemId,
            'intent_id' => $intentId,
            'intent_checksum' => $checksum,
            'recorded_at' => $recordedAt->format('Y-m-d H:i:s.uP'),
        ]);
    }

    public function read(string $queueItemId): ?ModerationQueueItemState
    {
        $statement = $this->connection->prepare(
            'SELECT queue_item_id,case_id,priority,category,state,lease_id,claim_owner_id,lease_expires_at,source_version,updated_at
             FROM moderation_reports.queue_items WHERE queue_item_id=CAST(:queue_item_id AS uuid)',
        );
        $statement->execute(['queue_item_id' => $queueItemId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->mapper->queue($row);
    }

    public function checkpoint(string $projectionName, int $checkpoint, DateTimeImmutable $updatedAt): ModerationPersistenceWriteResult
    {
        try {
            $statement = $this->connection->prepare(
                'INSERT INTO moderation_reports.queue_checkpoints(projection_name,checkpoint,updated_at)
                 VALUES(:projection_name,:checkpoint,CAST(:updated_at AS timestamptz))
                 ON CONFLICT(projection_name) DO UPDATE SET checkpoint=EXCLUDED.checkpoint,updated_at=EXCLUDED.updated_at
                 WHERE moderation_reports.queue_checkpoints.checkpoint < EXCLUDED.checkpoint',
            );
            $statement->execute([
                'projection_name' => $projectionName,
                'checkpoint' => $checkpoint,
                'updated_at' => $updatedAt->format('Y-m-d H:i:s.uP'),
            ]);
            if ($statement->rowCount() === 1) {
                return ModerationPersistenceWriteResult::Applied;
            }
            $read = $this->connection->prepare(
                'SELECT checkpoint FROM moderation_reports.queue_checkpoints WHERE projection_name=:projection_name',
            );
            $read->execute(['projection_name' => $projectionName]);
            $stored = (int) $read->fetchColumn();

            return $stored === $checkpoint
                ? ModerationPersistenceWriteResult::AlreadyApplied
                : ModerationPersistenceWriteResult::VersionConflict;
        } catch (PDOException) {
            return ModerationPersistenceWriteResult::Rejected;
        }
    }

    private function lock(string $identity): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:identity,0))');
        $statement->execute(['identity' => $identity]);
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
        $savepoint = 'moderation_queue_store';
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

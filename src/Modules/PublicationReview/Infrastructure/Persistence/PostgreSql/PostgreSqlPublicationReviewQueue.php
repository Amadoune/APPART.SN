<?php

namespace Appart\Modules\PublicationReview\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ListingLifecycle\Application\PublicationGateway\ListingPublicationCommandStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEvent;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventSerializer;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventType;
use Appart\Modules\PublicationReview\Application\Projection\Contract\PublicationReviewProjectionStore;
use Appart\Modules\PublicationReview\Application\Projection\ProjectionActivationStatus;
use Appart\Modules\PublicationReview\Application\Projection\ProjectPublishedListingResult;
use Appart\Modules\PublicationReview\Application\Projection\ProjectPublishedListingStatus;
use Appart\Modules\PublicationReview\Application\Queue\Contract\ClaimPublicationReviewV1;
use Appart\Modules\PublicationReview\Application\Queue\Contract\PublicationReviewCommandLedger;
use Appart\Modules\PublicationReview\Application\Queue\Contract\PublicationReviewQueue;
use Appart\Modules\PublicationReview\Application\Queue\Contract\PublicationReviewQueueReaderV1;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewClaimResult;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewClaimStatus;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewIngestionResult;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewQueueItem;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewQueueItemState;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewQueuePage;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewQueueReadStatus;
use Appart\Modules\PublicationReview\Application\Review\Contract\PublicationReviewCommandStore;
use Appart\Modules\PublicationReview\Application\Review\PublicationReviewCommandResult;
use Appart\Modules\PublicationReview\Application\Review\PublicationReviewCommandStatus;
use DateTimeImmutable;
use JsonException;
use PDO;
use Throwable;

final readonly class PostgreSqlPublicationReviewQueue implements ClaimPublicationReviewV1, PublicationReviewCommandLedger, PublicationReviewCommandStore, PublicationReviewProjectionStore, PublicationReviewQueue, PublicationReviewQueueReaderV1
{
    public function __construct(private PDO $connection) {}

    public function ingest(ListingPublicationEvent $event): PublicationReviewIngestionResult
    {
        if (! in_array($event->type, [ListingPublicationEventType::ListingSubmitted, ListingPublicationEventType::ListingResubmitted], true)) {
            return PublicationReviewIngestionResult::Ignored;
        }

        $canonical = (new ListingPublicationEventSerializer)->serialize($event);
        $checksum = hash('sha256', $canonical);

        try {
            return $this->transaction(function () use ($event, $checksum): PublicationReviewIngestionResult {
                $this->lock('ingest|'.$event->eventId->value);
                $existing = $this->selectItem($event->eventId->value, true);
                if ($existing !== false) {
                    return hash_equals((string) $existing['source_checksum'], $checksum)
                        ? PublicationReviewIngestionResult::AlreadyApplied
                        : PublicationReviewIngestionResult::DivergentMessage;
                }

                $statement = $this->connection->prepare('INSERT INTO publication_review.queue_items(queue_item_id,source_event_id,source_checksum,listing_id,submission_version,submitted_at,state,version) VALUES(:queue_item_id,:source_event_id,:source_checksum,CAST(:listing_id AS uuid),:submission_version,CAST(:submitted_at AS timestamptz),\'pending\',1)');
                $statement->execute([
                    'queue_item_id' => $event->eventId->value,
                    'source_event_id' => $event->eventId->value,
                    'source_checksum' => $checksum,
                    'listing_id' => $event->payload->listingId->value,
                    'submission_version' => $event->payload->publicationVersion,
                    'submitted_at' => $event->metadata->occurredAt->value,
                ]);

                return PublicationReviewIngestionResult::Applied;
            });
        } catch (Throwable) {
            return PublicationReviewIngestionResult::DependencyUnavailable;
        }
    }

    public function read(?string $afterCursor, int $limit): PublicationReviewQueuePage
    {
        if ($limit < 1 || $limit > 100) {
            return new PublicationReviewQueuePage(PublicationReviewQueueReadStatus::Corrupted);
        }
        try {
            $cursor = $this->decodeCursor($afterCursor);
            $sql = "SELECT * FROM publication_review.queue_items WHERE state='pending'";
            $parameters = [];
            if ($cursor !== null) {
                $sql .= ' AND (submitted_at,listing_id,submission_version,queue_item_id) > (CAST(:submitted_at AS timestamptz),CAST(:listing_id AS uuid),:submission_version,:queue_item_id)';
                $parameters = $cursor;
            }
            $sql .= ' ORDER BY submitted_at,listing_id,submission_version,queue_item_id LIMIT :limit';
            $statement = $this->connection->prepare($sql);
            foreach ($parameters as $name => $value) {
                $statement->bindValue($name, $value);
            }
            $statement->bindValue('limit', $limit + 1, PDO::PARAM_INT);
            $statement->execute();
            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
            $hasMore = count($rows) > $limit;
            $rows = array_slice($rows, 0, $limit);
            if ($rows === []) {
                return new PublicationReviewQueuePage(PublicationReviewQueueReadStatus::Empty);
            }
            $items = array_map($this->map(...), $rows);
            $next = $hasMore ? $this->encodeCursor($rows[array_key_last($rows)]) : null;

            return new PublicationReviewQueuePage(PublicationReviewQueueReadStatus::Available, $items, $next);
        } catch (Throwable) {
            return new PublicationReviewQueuePage(PublicationReviewQueueReadStatus::DependencyUnavailable);
        }
    }

    public function claim(string $commandId, string $queueItemId, string $actor, DateTimeImmutable $occurredAt, int $expectedVersion): PublicationReviewClaimResult
    {
        if (! $this->uuid($commandId) || trim($actor) === '' || $expectedVersion < 1) {
            return new PublicationReviewClaimResult(PublicationReviewClaimStatus::DivergentCommand);
        }
        $canonical = json_encode([$commandId, $queueItemId, $actor, $occurredAt->format('Y-m-d\TH:i:s.u\Z'), $expectedVersion], JSON_THROW_ON_ERROR);
        $checksum = hash('sha256', $canonical);
        try {
            return $this->transaction(function () use ($commandId, $queueItemId, $actor, $occurredAt, $expectedVersion, $checksum): PublicationReviewClaimResult {
                $this->lock('claim|'.$queueItemId);
                $recorded = $this->ledger($commandId, true);
                if ($recorded !== false) {
                    return new PublicationReviewClaimResult(
                        hash_equals((string) $recorded['command_checksum'], $checksum)
                            ? PublicationReviewClaimStatus::AlreadyApplied
                            : PublicationReviewClaimStatus::DivergentCommand,
                        $this->itemOrNull($queueItemId),
                    );
                }
                $row = $this->selectItem($queueItemId, true);
                if ($row === false) {
                    return new PublicationReviewClaimResult(PublicationReviewClaimStatus::Missing);
                }
                if ((int) $row['version'] !== $expectedVersion) {
                    return new PublicationReviewClaimResult(PublicationReviewClaimStatus::VersionConflict, $this->map($row));
                }
                if ((string) $row['state'] !== PublicationReviewQueueItemState::Pending->value) {
                    return new PublicationReviewClaimResult(PublicationReviewClaimStatus::Conflict, $this->map($row));
                }
                $update = $this->connection->prepare("UPDATE publication_review.queue_items SET state='claimed',claimed_by=:actor,claimed_at=:claimed_at,version=version+1 WHERE queue_item_id=:queue_item_id AND version=:expected_version AND state='pending'");
                $update->execute(['actor' => $actor, 'claimed_at' => $occurredAt->format('Y-m-d H:i:s.uP'), 'queue_item_id' => $queueItemId, 'expected_version' => $expectedVersion]);
                if ($update->rowCount() !== 1) {
                    return new PublicationReviewClaimResult(PublicationReviewClaimStatus::VersionConflict, $this->itemOrNull($queueItemId));
                }
                $ledger = $this->connection->prepare("INSERT INTO publication_review.command_ledger(command_id,command_checksum,queue_item_id,result_status,result_version,recorded_at) VALUES(CAST(:command_id AS uuid),:checksum,:queue_item_id,'applied',:result_version,:recorded_at)");
                $ledger->execute(['command_id' => $commandId, 'checksum' => $checksum, 'queue_item_id' => $queueItemId, 'result_version' => $expectedVersion + 1, 'recorded_at' => $occurredAt->format('Y-m-d H:i:s.uP')]);

                return new PublicationReviewClaimResult(PublicationReviewClaimStatus::Applied, $this->itemOrNull($queueItemId));
            });
        } catch (Throwable) {
            return new PublicationReviewClaimResult(PublicationReviewClaimStatus::DependencyUnavailable);
        }
    }

    public function hasRecorded(string $commandId): bool
    {
        return $this->ledger($commandId, false) !== false;
    }

    public function activate(string $listingId, int $publicationVersion, string $commandId, DateTimeImmutable $occurredAt, callable $activation): ProjectPublishedListingResult
    {
        if (! $this->uuid($listingId) || ! $this->uuid($commandId) || $publicationVersion < 1) {
            return new ProjectPublishedListingResult(ProjectPublishedListingStatus::Conflict);
        }
        $checksum = hash('sha256', json_encode(['project_published_listing', $listingId, $publicationVersion, $commandId, $occurredAt->format('Y-m-d\TH:i:s.uP')], JSON_THROW_ON_ERROR));

        try {
            return $this->transaction(function () use ($listingId, $publicationVersion, $commandId, $occurredAt, $activation, $checksum): ProjectPublishedListingResult {
                $this->lock('projection|'.$listingId.'|'.$publicationVersion);
                $recorded = $this->ledger($commandId, true);
                if ($recorded !== false) {
                    return new ProjectPublishedListingResult(
                        hash_equals((string) $recorded['command_checksum'], $checksum)
                            ? ProjectPublishedListingStatus::AlreadyApplied
                            : ProjectPublishedListingStatus::Conflict,
                        isset($recorded['result_version']) ? (int) $recorded['result_version'] : null,
                    );
                }

                $statement = $this->connection->prepare("SELECT * FROM publication_review.queue_items WHERE listing_id=CAST(:listing_id AS uuid) AND submission_version + 2=:publication_version AND state='completed' FOR UPDATE");
                $statement->execute(['listing_id' => $listingId, 'publication_version' => $publicationVersion]);
                $row = $statement->fetch(PDO::FETCH_ASSOC);
                if ($row === false) {
                    return new ProjectPublishedListingResult(ProjectPublishedListingStatus::NotReady);
                }
                $item = $this->map($row);
                $activated = $activation();
                if ($activated === ProjectionActivationStatus::NotReady) {
                    return new ProjectPublishedListingResult(ProjectPublishedListingStatus::NotReady, $item->version);
                }
                if ($activated === ProjectionActivationStatus::Conflict) {
                    return new ProjectPublishedListingResult(ProjectPublishedListingStatus::Conflict, $item->version);
                }
                if ($activated === ProjectionActivationStatus::DependencyUnavailable) {
                    return new ProjectPublishedListingResult(ProjectPublishedListingStatus::DependencyUnavailable, $item->version);
                }

                $resultVersion = $item->version + 1;
                $update = $this->connection->prepare("UPDATE publication_review.queue_items SET version=:result_version WHERE queue_item_id=:queue_item_id AND version=:expected_version AND state='completed'");
                $update->execute(['result_version' => $resultVersion, 'queue_item_id' => $item->queueItemId, 'expected_version' => $item->version]);
                if ($update->rowCount() !== 1) {
                    return new ProjectPublishedListingResult(ProjectPublishedListingStatus::Conflict, $item->version);
                }
                $status = $activated === ProjectionActivationStatus::Applied
                    ? ProjectPublishedListingStatus::Applied
                    : ProjectPublishedListingStatus::AlreadyApplied;
                $ledger = $this->connection->prepare('INSERT INTO publication_review.command_ledger(command_id,command_checksum,queue_item_id,result_status,result_version,recorded_at) VALUES(CAST(:command_id AS uuid),:checksum,:queue_item_id,:result_status,:result_version,:recorded_at)');
                $ledger->execute([
                    'command_id' => $commandId,
                    'checksum' => $checksum,
                    'queue_item_id' => $item->queueItemId,
                    'result_status' => $status->value,
                    'result_version' => $resultVersion,
                    'recorded_at' => $occurredAt->format('Y-m-d H:i:s.uP'),
                ]);

                return new ProjectPublishedListingResult($status, $resultVersion);
            });
        } catch (Throwable) {
            return new ProjectPublishedListingResult(ProjectPublishedListingStatus::DependencyUnavailable);
        }
    }

    public function execute(string $operation, string $queueItemId, string $commandId, int $expectedVersion, string $actor, DateTimeImmutable $occurredAt, bool $complete, callable $gateway): PublicationReviewCommandResult
    {
        if (! in_array($operation, ['begin_review', 'approve_and_publish'], true) || ! $this->uuid($commandId) || trim($actor) === '' || $expectedVersion < 1) {
            return new PublicationReviewCommandResult(PublicationReviewCommandStatus::DivergentCommand);
        }
        $checksum = hash('sha256', json_encode([$operation, $queueItemId, $commandId, $expectedVersion, $actor, $occurredAt->format('Y-m-d\TH:i:s.uP')], JSON_THROW_ON_ERROR));

        try {
            return $this->transaction(function () use ($queueItemId, $commandId, $expectedVersion, $actor, $occurredAt, $complete, $gateway, $checksum): PublicationReviewCommandResult {
                $this->lock('review-command|'.$queueItemId);
                $recorded = $this->ledger($commandId, true);
                if ($recorded !== false) {
                    return new PublicationReviewCommandResult(
                        hash_equals((string) $recorded['command_checksum'], $checksum)
                            ? PublicationReviewCommandStatus::AlreadyApplied
                            : PublicationReviewCommandStatus::DivergentCommand,
                        isset($recorded['result_version']) ? (int) $recorded['result_version'] : null,
                    );
                }
                $row = $this->selectItem($queueItemId, true);
                if ($row === false) {
                    return new PublicationReviewCommandResult(PublicationReviewCommandStatus::Missing);
                }
                $item = $this->map($row);
                if ($item->version !== $expectedVersion) {
                    return new PublicationReviewCommandResult(PublicationReviewCommandStatus::VersionConflict, $item->version);
                }
                if ($item->state !== PublicationReviewQueueItemState::Claimed || $item->claimedBy !== $actor) {
                    return new PublicationReviewCommandResult(PublicationReviewCommandStatus::StateConflict, $item->version);
                }

                $gatewayResult = $gateway($item);
                $reduced = match ($gatewayResult->status) {
                    ListingPublicationCommandStatus::Applied, ListingPublicationCommandStatus::AlreadyApplied => PublicationReviewCommandStatus::Applied,
                    ListingPublicationCommandStatus::VersionConflict => PublicationReviewCommandStatus::VersionConflict,
                    ListingPublicationCommandStatus::StateConflict => PublicationReviewCommandStatus::StateConflict,
                    ListingPublicationCommandStatus::Missing => PublicationReviewCommandStatus::Missing,
                    ListingPublicationCommandStatus::DivergentCommand => PublicationReviewCommandStatus::DivergentCommand,
                    ListingPublicationCommandStatus::DependencyUnavailable => PublicationReviewCommandStatus::DependencyUnavailable,
                    default => PublicationReviewCommandStatus::GatewayRejected,
                };
                if ($reduced !== PublicationReviewCommandStatus::Applied) {
                    return new PublicationReviewCommandResult($reduced, $item->version);
                }

                $resultVersion = $item->version + 1;
                $update = $this->connection->prepare('UPDATE publication_review.queue_items SET state=:state,version=:result_version WHERE queue_item_id=:queue_item_id AND version=:expected_version');
                $update->execute([
                    'state' => $complete ? PublicationReviewQueueItemState::Completed->value : PublicationReviewQueueItemState::Claimed->value,
                    'result_version' => $resultVersion,
                    'queue_item_id' => $item->queueItemId,
                    'expected_version' => $expectedVersion,
                ]);
                if ($update->rowCount() !== 1) {
                    return new PublicationReviewCommandResult(PublicationReviewCommandStatus::VersionConflict, $item->version);
                }
                $ledger = $this->connection->prepare("INSERT INTO publication_review.command_ledger(command_id,command_checksum,queue_item_id,result_status,result_version,recorded_at) VALUES(CAST(:command_id AS uuid),:checksum,:queue_item_id,'applied',:result_version,:recorded_at)");
                $ledger->execute([
                    'command_id' => $commandId,
                    'checksum' => $checksum,
                    'queue_item_id' => $item->queueItemId,
                    'result_version' => $resultVersion,
                    'recorded_at' => $occurredAt->format('Y-m-d H:i:s.uP'),
                ]);

                return new PublicationReviewCommandResult(PublicationReviewCommandStatus::Applied, $resultVersion);
            });
        } catch (Throwable) {
            return new PublicationReviewCommandResult(PublicationReviewCommandStatus::DependencyUnavailable);
        }
    }

    /** @return array<string, mixed>|false */
    private function selectItem(string $queueItemId, bool $forUpdate): array|false
    {
        $statement = $this->connection->prepare('SELECT * FROM publication_review.queue_items WHERE queue_item_id=:queue_item_id'.($forUpdate ? ' FOR UPDATE' : ''));
        $statement->execute(['queue_item_id' => $queueItemId]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    private function itemOrNull(string $queueItemId): ?PublicationReviewQueueItem
    {
        $row = $this->selectItem($queueItemId, false);

        return $row === false ? null : $this->map($row);
    }

    /** @return array<string, mixed>|false */
    private function ledger(string $commandId, bool $forUpdate): array|false
    {
        $statement = $this->connection->prepare('SELECT * FROM publication_review.command_ledger WHERE command_id=CAST(:command_id AS uuid)'.($forUpdate ? ' FOR UPDATE' : ''));
        $statement->execute(['command_id' => $commandId]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /** @param array<string, mixed> $row */
    private function map(array $row): PublicationReviewQueueItem
    {
        return new PublicationReviewQueueItem(
            (string) $row['queue_item_id'],
            (string) $row['source_event_id'],
            (string) $row['listing_id'],
            (int) $row['submission_version'],
            new DateTimeImmutable((string) $row['submitted_at']),
            PublicationReviewQueueItemState::from((string) $row['state']),
            (int) $row['version'],
            isset($row['claimed_by']) ? (string) $row['claimed_by'] : null,
            isset($row['claimed_at']) ? new DateTimeImmutable((string) $row['claimed_at']) : null,
        );
    }

    /** @return array<string, string|int>|null */
    private function decodeCursor(?string $cursor): ?array
    {
        if ($cursor === null) {
            return null;
        }
        $decoded = base64_decode(strtr($cursor, '-_', '+/'), true);
        $data = is_string($decoded) ? json_decode($decoded, true, flags: JSON_THROW_ON_ERROR) : null;
        if (! is_array($data) || array_keys($data) !== ['submitted_at', 'listing_id', 'submission_version', 'queue_item_id']) {
            throw new JsonException('Invalid Publication Review cursor.');
        }

        return $data;
    }

    /** @param array<string, mixed> $row */
    private function encodeCursor(array $row): string
    {
        $json = json_encode([
            'submitted_at' => (string) $row['submitted_at'],
            'listing_id' => (string) $row['listing_id'],
            'submission_version' => (int) $row['submission_version'],
            'queue_item_id' => (string) $row['queue_item_id'],
        ], JSON_THROW_ON_ERROR);

        return rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
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
        $savepoint = 'publication_review_local';
        $owner ? $this->connection->beginTransaction() : $this->connection->exec("SAVEPOINT {$savepoint}");
        try {
            $result = $operation();
            $owner ? $this->connection->commit() : $this->connection->exec("RELEASE SAVEPOINT {$savepoint}");

            return $result;
        } catch (Throwable $error) {
            $owner ? $this->connection->rollBack() : $this->connection->exec("ROLLBACK TO SAVEPOINT {$savepoint}");
            throw $error;
        }
    }

    private function lock(string $identity): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:identity,0))');
        $statement->execute(['identity' => $identity]);
    }

    private function uuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) === 1;
    }
}

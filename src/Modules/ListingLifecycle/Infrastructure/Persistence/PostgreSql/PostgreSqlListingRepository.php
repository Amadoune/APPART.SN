<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Domain\Exception\ConcurrentListingModification;
use Appart\Modules\ListingLifecycle\Domain\Exception\ListingIdConflict;
use Appart\Modules\ListingLifecycle\Domain\Model\Listing;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingRevisionSnapshot;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingSnapshot;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingTransaction;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PersistentListingIntegrity;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlListingRepository implements ListingRegistry
{
    private ListingTransaction $transaction;

    public function __construct(private PDO $connection, private ListingMapper $mapper, ?ListingTransaction $transaction = null)
    {
        $this->transaction = $transaction ?? new PostgreSqlListingTransaction($connection);
    }

    public function find(ListingId $id): ?Listing
    {
        $statement = $this->connection->prepare('SELECT id, property_id, status, last_changed_at, last_changed_at_offset, expiration_date, expiration_date_offset, version FROM listing_lifecycle.listings WHERE id = :id');
        $statement->execute(['id' => $id->value]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->mapper->toAggregate($this->snapshotFromRow($row));
    }

    public function add(Listing $listing): void
    {
        $snapshot = $this->mapper->toSnapshot($listing);
        try {
            $this->transaction->run(function () use ($snapshot): void {
                $this->insertRoot($snapshot);
                foreach ($snapshot->revisions as $revision) {
                    $this->insertRevision($snapshot->id, $revision);
                }
            });
        } catch (PDOException $error) {
            if ($error->getCode() === '23505') {
                throw new ListingIdConflict;
            }
            throw PersistentListingIntegrity::invalid('write');
        } catch (ListingIdConflict $error) {
            throw $error;
        } catch (Throwable) {
            throw PersistentListingIntegrity::invalid('write');
        }
    }

    public function save(Listing $listing, int $expectedVersion): void
    {
        $snapshot = $this->mapper->toSnapshot($listing);
        if ($snapshot->version <= $expectedVersion) {
            throw new ConcurrentListingModification;
        }
        try {
            $this->transaction->run(function () use ($snapshot, $expectedVersion): void {
                $current = $this->revisions($snapshot->id);
                $this->assertAppendOnlyPrefix($current, $snapshot->revisions);
                $statement = $this->connection->prepare('UPDATE listing_lifecycle.listings SET property_id = :property_id, status = :status, last_changed_at = :last_changed_at, last_changed_at_offset = :last_changed_at_offset, expiration_date = :expiration_date, expiration_date_offset = :expiration_date_offset, version = :version WHERE id = :id AND version = :expected_version');
                $statement->execute($this->rootParameters($snapshot) + ['expected_version' => $expectedVersion]);
                if ($statement->rowCount() !== 1) {
                    throw new ConcurrentListingModification;
                }
                foreach (array_slice($snapshot->revisions, count($current)) as $revision) {
                    $this->insertRevision($snapshot->id, $revision);
                }
            });
        } catch (ConcurrentListingModification|PersistentListingIntegrity $error) {
            throw $error;
        } catch (Throwable) {
            throw PersistentListingIntegrity::invalid('write');
        }
    }

    private function insertRoot(ListingSnapshot $snapshot): void
    {
        $statement = $this->connection->prepare('INSERT INTO listing_lifecycle.listings (id, property_id, status, last_changed_at, last_changed_at_offset, expiration_date, expiration_date_offset, version) VALUES (:id, :property_id, :status, :last_changed_at, :last_changed_at_offset, :expiration_date, :expiration_date_offset, :version)');
        $statement->execute($this->rootParameters($snapshot));
    }

    /** @return array<string, int|string|null> */
    private function rootParameters(ListingSnapshot $snapshot): array
    {
        return ['id' => $snapshot->id, 'property_id' => $snapshot->propertyId, 'status' => $snapshot->status, 'last_changed_at' => $snapshot->lastChangedAt, 'last_changed_at_offset' => $this->offset($snapshot->lastChangedAt), 'expiration_date' => $snapshot->expirationDate, 'expiration_date_offset' => $snapshot->expirationDate === null ? null : $this->offset($snapshot->expirationDate), 'version' => $snapshot->version];
    }

    private function insertRevision(string $listingId, ListingRevisionSnapshot $revision): void
    {
        $statement = $this->connection->prepare('INSERT INTO listing_lifecycle.listing_revisions (listing_id, sequence, revision_id, previous_status, status, actor_id, trigger, reason, origin, occurred_at, occurred_at_offset) VALUES (:listing_id, :sequence, :revision_id, :previous_status, :status, :actor_id, :trigger, :reason, :origin, :occurred_at, :occurred_at_offset)');
        $statement->execute(['listing_id' => $listingId, 'sequence' => $revision->sequence, 'revision_id' => $revision->id, 'previous_status' => $revision->previousStatus, 'status' => $revision->status, 'actor_id' => $revision->actorId, 'trigger' => $revision->trigger, 'reason' => $revision->reason, 'origin' => $revision->origin, 'occurred_at' => $revision->occurredAt, 'occurred_at_offset' => $this->offset($revision->occurredAt)]);
    }

    /** @param array<string, mixed> $row */
    private function snapshotFromRow(array $row): ListingSnapshot
    {
        return new ListingSnapshot(
            (string) $row['id'],
            (string) $row['property_id'],
            (string) $row['status'],
            $this->normalizeDate((string) $row['last_changed_at'], (int) $row['last_changed_at_offset']),
            $row['expiration_date'] === null ? null : $this->normalizeDate((string) $row['expiration_date'], (int) $row['expiration_date_offset']),
            (int) $row['version'],
            $this->revisions((string) $row['id']),
        );
    }

    /** @return list<ListingRevisionSnapshot> */
    private function revisions(string $id): array
    {
        $statement = $this->connection->prepare('SELECT sequence, revision_id, previous_status, status, actor_id, trigger, reason, origin, occurred_at, occurred_at_offset FROM listing_lifecycle.listing_revisions WHERE listing_id = :id ORDER BY sequence');
        $statement->execute(['id' => $id]);

        return array_map(fn (array $row): ListingRevisionSnapshot => new ListingRevisionSnapshot((int) $row['sequence'], (string) $row['revision_id'], $row['previous_status'] === null ? null : (string) $row['previous_status'], (string) $row['status'], (string) $row['actor_id'], (string) $row['trigger'], $row['reason'] === null ? null : (string) $row['reason'], (string) $row['origin'], $this->normalizeDate((string) $row['occurred_at'], (int) $row['occurred_at_offset'])), $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /** @param list<ListingRevisionSnapshot> $current
     * @param  list<ListingRevisionSnapshot>  $candidate
     */
    private function assertAppendOnlyPrefix(array $current, array $candidate): void
    {
        if (count($current) > count($candidate)) {
            throw PersistentListingIntegrity::invalid('history regression');
        }
        foreach ($current as $index => $revision) {
            if ($revision != $candidate[$index]) {
                throw PersistentListingIntegrity::invalid('history rewrite');
            }
        }
    }

    private function normalizeDate(string $value, int $offset): string
    {
        try {
            $sign = $offset < 0 ? '-' : '+';
            $absolute = abs($offset);
            $timezone = new \DateTimeZone(sprintf('%s%02d:%02d', $sign, intdiv($absolute, 60), $absolute % 60));

            return (new \DateTimeImmutable($value))->setTimezone($timezone)->format('Y-m-d\TH:i:s.uP');
        } catch (Throwable) {
            throw PersistentListingIntegrity::invalid('date');
        }
    }

    private function offset(string $value): int
    {
        return intdiv((new \DateTimeImmutable($value))->getOffset(), 60);
    }
}

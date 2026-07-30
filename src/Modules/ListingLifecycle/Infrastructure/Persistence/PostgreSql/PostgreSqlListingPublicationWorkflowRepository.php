<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceReadResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceWriteResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingPublicationWorkflowMapper;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlListingPublicationWorkflowRepository implements ListingPublicationWorkflowStore
{
    public function __construct(private PDO $connection, private ListingPublicationWorkflowMapper $mapper) {}

    public function initialize(ListingId $listingId, ListingPublicationState $state): ListingPublicationPersistenceWriteResult
    {
        return $this->withinTransaction(function () use ($listingId, $state): ListingPublicationPersistenceWriteResult {
            $this->lock($listingId);
            $current = $this->current($listingId, true);
            $parameters = $this->mapper->initial($listingId, $state);
            if ($current !== false) {
                return hash_equals((string) $current['transition_checksum'], $parameters['checksum'])
                    ? ListingPublicationPersistenceWriteResult::AlreadyApplied
                    : ListingPublicationPersistenceWriteResult::StateConflict;
            }
            $this->insert($parameters);

            return ListingPublicationPersistenceWriteResult::Applied;
        });
    }

    public function append(ListingId $listingId, ListingPublicationTransition $transition, int $version): ListingPublicationPersistenceWriteResult
    {
        return $this->withinTransaction(function () use ($listingId, $transition, $version): ListingPublicationPersistenceWriteResult {
            $this->lock($listingId);
            $current = $this->current($listingId, true);
            if ($current === false) {
                return ListingPublicationPersistenceWriteResult::RejectedVersion;
            }
            $parameters = $this->mapper->transition($listingId, $transition, $version);
            if ($version === (int) $current['version']) {
                return hash_equals((string) $current['transition_checksum'], $parameters['checksum'])
                    ? ListingPublicationPersistenceWriteResult::AlreadyApplied
                    : ListingPublicationPersistenceWriteResult::StateConflict;
            }
            if ($version !== (int) $current['version'] + 1) {
                return ListingPublicationPersistenceWriteResult::RejectedVersion;
            }
            if ((string) $current['current_state'] !== $transition->from->value) {
                return ListingPublicationPersistenceWriteResult::StateConflict;
            }
            try {
                $this->insert($parameters);
            } catch (PDOException $error) {
                if ($error->getCode() === '23514') {
                    return ListingPublicationPersistenceWriteResult::TransitionRejected;
                }
                throw $error;
            }

            return ListingPublicationPersistenceWriteResult::Applied;
        });
    }

    public function read(ListingId $listingId): ListingPublicationPersistenceReadResult
    {
        $row = $this->current($listingId, false);
        if ($row === false) {
            return ListingPublicationPersistenceReadResult::missing($listingId);
        }
        try {
            return ListingPublicationPersistenceReadResult::found($this->mapper->snapshot($row));
        } catch (Throwable) {
            return ListingPublicationPersistenceReadResult::corrupted($listingId);
        }
    }

    /** @param callable(): ListingPublicationPersistenceWriteResult $operation */
    private function withinTransaction(callable $operation): ListingPublicationPersistenceWriteResult
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }
        try {
            $result = $operation();
            if ($owner) {
                $this->connection->commit();
            }

            return $result;
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            }
            throw $error;
        }
    }

    private function lock(ListingId $listingId): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:listing_id,0))');
        $statement->execute(['listing_id' => $listingId->value]);
    }

    /** @return array<string, mixed>|false */
    private function current(ListingId $listingId, bool $forUpdate): array|false
    {
        $sql = 'SELECT listing_id::text,version,previous_state,current_state,action,transition_checksum FROM listing_lifecycle.publication_workflow_transitions WHERE listing_id=:listing_id ORDER BY version DESC LIMIT 1';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }
        $statement = $this->connection->prepare($sql);
        $statement->execute(['listing_id' => $listingId->value]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /** @param array<string, mixed> $parameters */
    private function insert(array $parameters): void
    {
        $statement = $this->connection->prepare('INSERT INTO listing_lifecycle.publication_workflow_transitions(listing_id,version,previous_state,current_state,action,transition_checksum) VALUES(CAST(:listing_id AS uuid),:version,:previous_state,:current_state,:action,:checksum)');
        $statement->execute($parameters);
    }
}

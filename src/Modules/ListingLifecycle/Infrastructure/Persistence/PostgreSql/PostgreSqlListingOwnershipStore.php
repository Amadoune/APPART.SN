<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\AuthoringPersistenceWriteResult;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\ListingOwnershipStore;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\ListingOwnershipState;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingOwnershipMapper;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlListingOwnershipStore implements ListingOwnershipStore
{
    public function __construct(private PDO $connection, private ListingOwnershipMapper $mapper) {}

    public function read(string $listingId): ?ListingOwnershipState
    {
        $statement = $this->connection->prepare('SELECT * FROM listing_authoring.ownerships WHERE listing_id=:id');
        $statement->execute(['id' => $listingId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        $delegations = $this->delegations($listingId);

        return $this->mapper->toState($row, $delegations);
    }

    public function save(ListingOwnershipState $state, int $expectedVersion): AuthoringPersistenceWriteResult
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }
        try {
            $this->lock($state->listingId);
            $current = $this->read($state->listingId);
            $result = $this->decision($state, $current, $expectedVersion);
            if ($result === AuthoringPersistenceWriteResult::Applied) {
                $this->writeRoot($state, $current === null);
                $this->replaceDelegations($state);
            }
            if ($owner) {
                $this->connection->commit();
            }

            return $result;
        } catch (PDOException $error) {
            $this->rollback($owner);

            return $error->getCode() === '23505'
                ? AuthoringPersistenceWriteResult::IdentityConflict
                : AuthoringPersistenceWriteResult::Rejected;
        } catch (Throwable $error) {
            $this->rollback($owner);
            throw $error;
        }
    }

    private function decision(ListingOwnershipState $candidate, ?ListingOwnershipState $current, int $expected): AuthoringPersistenceWriteResult
    {
        $currentVersion = $current === null ? 0 : $current->version;
        if ($current !== null && $current->intentId === $candidate->intentId) {
            return hash_equals($current->intentChecksum, $candidate->intentChecksum)
                ? AuthoringPersistenceWriteResult::AlreadyApplied
                : AuthoringPersistenceWriteResult::DivergentIntent;
        }
        if ($current !== null && ($current->ownerAccountId !== $candidate->ownerAccountId || $current->propertyId !== $candidate->propertyId)) {
            return AuthoringPersistenceWriteResult::Rejected;
        }
        if ($currentVersion !== $expected || $candidate->version !== $expected + 1) {
            return AuthoringPersistenceWriteResult::VersionConflict;
        }

        return AuthoringPersistenceWriteResult::Applied;
    }

    /** @return array<string, list<string>> */
    private function delegations(string $listingId): array
    {
        $statement = $this->connection->prepare('SELECT delegate_account_id,permission FROM listing_authoring.delegations WHERE listing_id=:id ORDER BY delegate_account_id,permission');
        $statement->execute(['id' => $listingId]);
        $delegations = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $delegations[(string) $row['delegate_account_id']][] = (string) $row['permission'];
        }

        return $delegations;
    }

    private function writeRoot(ListingOwnershipState $state, bool $insert): void
    {
        $sql = $insert
            ? 'INSERT INTO listing_authoring.ownerships(listing_id,property_id,owner_account_id,version,last_intent_id,last_intent_checksum) VALUES(:listing_id,:property_id,:owner_account_id,:version,:intent_id,:checksum)'
            : 'UPDATE listing_authoring.ownerships SET version=:version,last_intent_id=:intent_id,last_intent_checksum=:checksum,updated_at=CURRENT_TIMESTAMP WHERE listing_id=:listing_id AND property_id=:property_id AND owner_account_id=:owner_account_id';
        $statement = $this->connection->prepare($sql);
        $statement->execute(['listing_id' => $state->listingId, 'property_id' => $state->propertyId, 'owner_account_id' => $state->ownerAccountId, 'version' => $state->version, 'intent_id' => $state->intentId, 'checksum' => $state->intentChecksum]);
    }

    private function replaceDelegations(ListingOwnershipState $state): void
    {
        $delete = $this->connection->prepare('DELETE FROM listing_authoring.delegations WHERE listing_id=:id');
        $delete->execute(['id' => $state->listingId]);
        $insert = $this->connection->prepare('INSERT INTO listing_authoring.delegations(listing_id,delegate_account_id,permission) VALUES(:listing_id,:account_id,:permission)');
        foreach ($state->delegations as $accountId => $permissions) {
            foreach (array_values(array_unique($permissions)) as $permission) {
                $insert->execute(['listing_id' => $state->listingId, 'account_id' => $accountId, 'permission' => $permission]);
            }
        }
    }

    private function lock(string $id): void
    {
        $statement = $this->connection->prepare("SELECT pg_advisory_xact_lock(hashtextextended('listing-ownership:' || :id,0))");
        $statement->execute(['id' => $id]);
    }

    private function rollback(bool $owner): void
    {
        if ($owner && $this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }
}

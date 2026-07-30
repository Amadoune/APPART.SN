<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\AuthoringPersistenceWriteResult;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\ListingDraftStore;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\ListingDraftState;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingDraftMapper;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlListingDraftStore implements ListingDraftStore
{
    public function __construct(private PDO $connection, private ListingDraftMapper $mapper) {}

    public function read(string $listingId): ?ListingDraftState
    {
        $statement = $this->connection->prepare('SELECT * FROM listing_authoring.drafts WHERE listing_id=:id');
        $statement->execute(['id' => $listingId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->mapper->toState($row);
    }

    public function save(ListingDraftState $state, int $expectedVersion): AuthoringPersistenceWriteResult
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
                $this->appendRevision($state);
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

    private function decision(ListingDraftState $candidate, ?ListingDraftState $current, int $expected): AuthoringPersistenceWriteResult
    {
        $currentVersion = $current === null ? 0 : $current->version;
        if ($current !== null && $current->intentId === $candidate->intentId) {
            return hash_equals($current->intentChecksum, $candidate->intentChecksum)
                ? AuthoringPersistenceWriteResult::AlreadyApplied
                : AuthoringPersistenceWriteResult::DivergentIntent;
        }
        if ($current !== null && $current->propertyId !== $candidate->propertyId) {
            return AuthoringPersistenceWriteResult::Rejected;
        }
        if ($currentVersion !== $expected || $candidate->version !== $expected + 1) {
            return AuthoringPersistenceWriteResult::VersionConflict;
        }

        return AuthoringPersistenceWriteResult::Applied;
    }

    private function writeRoot(ListingDraftState $state, bool $insert): void
    {
        $columns = 'listing_id,property_id,title,description,transaction_kind,price_minor,currency,charges_minor,availability_date,contact_preference,version,last_intent_id,last_intent_checksum';
        $values = ':listing_id,:property_id,:title,:description,:transaction_kind,:price_minor,:currency,:charges_minor,:availability_date,:contact_preference,:version,:intent_id,:checksum';
        $sql = $insert
            ? "INSERT INTO listing_authoring.drafts({$columns}) VALUES({$values})"
            : 'UPDATE listing_authoring.drafts SET title=:title,description=:description,transaction_kind=:transaction_kind,price_minor=:price_minor,currency=:currency,charges_minor=:charges_minor,availability_date=:availability_date,contact_preference=:contact_preference,version=:version,last_intent_id=:intent_id,last_intent_checksum=:checksum,updated_at=CURRENT_TIMESTAMP WHERE listing_id=:listing_id AND property_id=:property_id';
        $statement = $this->connection->prepare($sql);
        $statement->execute($this->parameters($state));
    }

    private function appendRevision(ListingDraftState $state): void
    {
        $statement = $this->connection->prepare('INSERT INTO listing_authoring.draft_revisions(listing_id,version,intent_id,intent_checksum) VALUES(:listing_id,:version,:intent_id,:checksum)');
        $statement->execute(['listing_id' => $state->listingId, 'version' => $state->version, 'intent_id' => $state->intentId, 'checksum' => $state->intentChecksum]);
    }

    /** @return array<string, int|string|null> */
    private function parameters(ListingDraftState $state): array
    {
        return ['listing_id' => $state->listingId, 'property_id' => $state->propertyId, 'title' => $state->title, 'description' => $state->description, 'transaction_kind' => $state->transactionKind, 'price_minor' => $state->priceMinor, 'currency' => $state->currency, 'charges_minor' => $state->chargesMinor, 'availability_date' => $state->availabilityDate, 'contact_preference' => $state->contactPreference, 'version' => $state->version, 'intent_id' => $state->intentId, 'checksum' => $state->intentChecksum];
    }

    private function lock(string $id): void
    {
        $statement = $this->connection->prepare("SELECT pg_advisory_xact_lock(hashtextextended('listing-draft:' || :id,0))");
        $statement->execute(['id' => $id]);
    }

    private function rollback(bool $owner): void
    {
        if ($owner && $this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }
}

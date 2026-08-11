<?php

namespace Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\Contract\PropertyAuthoringStore;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringPersistenceWriteResult;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyAuthoringMapper;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlPropertyAuthoringStore implements PropertyAuthoringStore
{
    public function __construct(private PDO $connection, private PropertyAuthoringMapper $mapper) {}

    public function read(string $propertyId): ?PropertyAuthoringState
    {
        $statement = $this->connection->prepare('SELECT property_id, owner_account_id, version, last_intent_id, last_intent_checksum, property_type, city, neighborhood FROM real_estate_catalog_authoring.property_authoring WHERE property_id=:id');
        $statement->execute(['id' => $propertyId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->mapper->toState($row);
    }

    public function save(PropertyAuthoringState $state, int $expectedVersion): PropertyAuthoringPersistenceWriteResult
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }
        try {
            $this->lock($state->propertyId);
            $current = $this->read($state->propertyId);
            $result = $this->decision($state, $current, $expectedVersion);
            if ($result === PropertyAuthoringPersistenceWriteResult::Applied) {
                $this->write($state, $current === null);
            }
            if ($owner) {
                $this->connection->commit();
            }

            return $result;
        } catch (PDOException $error) {
            $this->rollback($owner);

            return $error->getCode() === '23505'
                ? PropertyAuthoringPersistenceWriteResult::IdentityConflict
                : PropertyAuthoringPersistenceWriteResult::Rejected;
        } catch (Throwable $error) {
            $this->rollback($owner);
            throw $error;
        }
    }

    private function decision(PropertyAuthoringState $candidate, ?PropertyAuthoringState $current, int $expected): PropertyAuthoringPersistenceWriteResult
    {
        $currentVersion = $current === null ? 0 : $current->version;
        if ($current !== null && $current->intentId === $candidate->intentId) {
            return hash_equals($current->intentChecksum, $candidate->intentChecksum)
                ? PropertyAuthoringPersistenceWriteResult::AlreadyApplied
                : PropertyAuthoringPersistenceWriteResult::DivergentIntent;
        }
        if ($current !== null && $current->ownerAccountId !== $candidate->ownerAccountId) {
            return PropertyAuthoringPersistenceWriteResult::Rejected;
        }
        if ($currentVersion !== $expected || $candidate->version !== $expected + 1) {
            return PropertyAuthoringPersistenceWriteResult::VersionConflict;
        }

        return PropertyAuthoringPersistenceWriteResult::Applied;
    }

    private function lock(string $id): void
    {
        $statement = $this->connection->prepare("SELECT pg_advisory_xact_lock(hashtextextended('property-authoring:' || :id,0))");
        $statement->execute(['id' => $id]);
    }

    private function write(PropertyAuthoringState $state, bool $insert): void
    {
        $sql = $insert
            ? 'INSERT INTO real_estate_catalog_authoring.property_authoring(property_id,owner_account_id,version,last_intent_id,last_intent_checksum,property_type,city,neighborhood) VALUES(:property_id,:owner_account_id,:version,:intent_id,:checksum,:property_type,:city,:neighborhood)'
            : 'UPDATE real_estate_catalog_authoring.property_authoring SET version=:version,last_intent_id=:intent_id,last_intent_checksum=:checksum,property_type=:property_type,city=:city,neighborhood=:neighborhood,updated_at=CURRENT_TIMESTAMP WHERE property_id=:property_id AND owner_account_id=:owner_account_id';
        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'property_id' => $state->propertyId,
            'owner_account_id' => $state->ownerAccountId,
            'version' => $state->version,
            'intent_id' => $state->intentId,
            'checksum' => $state->intentChecksum,
            'property_type' => $state->propertyType,
            'city' => $state->city,
            'neighborhood' => $state->neighborhood,
        ]);
    }

    private function rollback(bool $owner): void
    {
        $active = $this->connection->inTransaction();
        if ($owner && $active) {
            $this->connection->rollBack();
        }
    }
}

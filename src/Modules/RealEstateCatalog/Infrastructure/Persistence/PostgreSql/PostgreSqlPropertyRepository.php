<?php

namespace Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry;
use Appart\Modules\RealEstateCatalog\Domain\Exception\ConcurrentPropertyModification;
use Appart\Modules\RealEstateCatalog\Domain\Exception\PropertyIdConflict;
use Appart\Modules\RealEstateCatalog\Domain\Exception\PropertyReferenceConflict;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\AddressSnapshot;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PersistentPropertyIntegrity;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyMapper;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertySnapshot;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyTransaction;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlPropertyRepository implements PropertyRegistry
{
    private PropertyTransaction $transaction;

    public function __construct(private PDO $connection, private PropertyMapper $mapper, ?PropertyTransaction $transaction = null)
    {
        $this->transaction = $transaction ?? new PostgreSqlPropertyTransaction($connection);
    }

    public function find(PropertyId $id): ?Property
    {
        $statement = $this->connection->prepare('SELECT id, reference, type, surface, rooms, bathrooms, construction_year, status, last_changed_at, last_changed_at_offset, version FROM real_estate_catalog.properties WHERE id = :id');
        $statement->execute(['id' => $id->value]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->mapper->toAggregate($this->snapshotFromRow($row));
    }

    public function add(Property $property): void
    {
        $snapshot = $this->mapper->toSnapshot($property);
        try {
            $this->transaction->run(function () use ($snapshot): void {
                try {
                    $this->insertRoot($snapshot);
                } catch (PDOException $error) {
                    if ($error->getCode() === '23505') {
                        throw new PropertyIdConflict;
                    }
                    throw $error;
                }
                try {
                    $statement = $this->connection->prepare('INSERT INTO real_estate_catalog.property_reference_reservations (reference, property_id) VALUES (:reference, :property_id)');
                    $statement->execute(['reference' => $snapshot->reference, 'property_id' => $snapshot->id]);
                } catch (PDOException $error) {
                    if ($error->getCode() === '23505') {
                        throw new PropertyReferenceConflict;
                    }
                    throw $error;
                }
                $this->writeAddress($snapshot->id, $snapshot->address);
            });
        } catch (PropertyIdConflict|PropertyReferenceConflict $error) {
            throw $error;
        } catch (Throwable) {
            throw PersistentPropertyIntegrity::invalid('write');
        }
    }

    public function save(Property $property, int $expectedVersion): void
    {
        $snapshot = $this->mapper->toSnapshot($property);
        if ($snapshot->version <= $expectedVersion) {
            throw new ConcurrentPropertyModification;
        }
        try {
            $this->transaction->run(function () use ($snapshot, $expectedVersion): void {
                $statement = $this->connection->prepare('UPDATE real_estate_catalog.properties SET type = :type, surface = :surface, rooms = :rooms, bathrooms = :bathrooms, construction_year = :construction_year, status = :status, last_changed_at = :last_changed_at, last_changed_at_offset = :last_changed_at_offset, version = :version WHERE id = :id AND reference = :reference AND version = :expected_version');
                $statement->execute($this->rootParameters($snapshot) + ['expected_version' => $expectedVersion]);
                if ($statement->rowCount() !== 1) {
                    throw new ConcurrentPropertyModification;
                }
                $this->writeAddress($snapshot->id, $snapshot->address);
            });
        } catch (ConcurrentPropertyModification $error) {
            throw $error;
        } catch (Throwable) {
            throw PersistentPropertyIntegrity::invalid('write');
        }
    }

    private function insertRoot(PropertySnapshot $snapshot): void
    {
        $statement = $this->connection->prepare('INSERT INTO real_estate_catalog.properties (id, reference, type, surface, rooms, bathrooms, construction_year, status, last_changed_at, last_changed_at_offset, version) VALUES (:id, :reference, :type, :surface, :rooms, :bathrooms, :construction_year, :status, :last_changed_at, :last_changed_at_offset, :version)');
        $statement->execute($this->rootParameters($snapshot));
    }

    /** @return array<string, int|string|null> */
    private function rootParameters(PropertySnapshot $snapshot): array
    {
        return ['id' => $snapshot->id, 'reference' => $snapshot->reference, 'type' => $snapshot->type, 'surface' => $snapshot->surface, 'rooms' => $snapshot->rooms, 'bathrooms' => $snapshot->bathrooms, 'construction_year' => $snapshot->constructionYear, 'status' => $snapshot->status, 'last_changed_at' => $snapshot->lastChangedAt, 'last_changed_at_offset' => $this->offset($snapshot->lastChangedAt), 'version' => $snapshot->version];
    }

    private function writeAddress(string $propertyId, ?AddressSnapshot $address): void
    {
        if ($address === null) {
            $statement = $this->connection->prepare('DELETE FROM real_estate_catalog.property_addresses WHERE property_id = :property_id');
            $statement->execute(['property_id' => $propertyId]);

            return;
        }
        $statement = $this->connection->prepare('INSERT INTO real_estate_catalog.property_addresses (property_id, address_id, geographic_place_id, address_line) VALUES (:property_id, :address_id, :geographic_place_id, :address_line) ON CONFLICT (property_id) DO UPDATE SET address_id = EXCLUDED.address_id, geographic_place_id = EXCLUDED.geographic_place_id, address_line = EXCLUDED.address_line');
        $statement->execute(['property_id' => $propertyId, 'address_id' => $address->id, 'geographic_place_id' => $address->placeId, 'address_line' => $address->line]);
    }

    /** @param array<string, mixed> $row */
    private function snapshotFromRow(array $row): PropertySnapshot
    {
        $statement = $this->connection->prepare('SELECT address_id, geographic_place_id, address_line FROM real_estate_catalog.property_addresses WHERE property_id = :id');
        $statement->execute(['id' => (string) $row['id']]);
        $address = $statement->fetch(PDO::FETCH_ASSOC);

        return new PropertySnapshot(
            (string) $row['id'],
            (string) $row['reference'],
            (string) $row['type'],
            $row['surface'] === null ? null : (int) $row['surface'],
            (int) $row['rooms'],
            (int) $row['bathrooms'],
            $row['construction_year'] === null ? null : (int) $row['construction_year'],
            $address === false ? null : new AddressSnapshot((string) $address['address_id'], (string) $address['geographic_place_id'], (string) $address['address_line']),
            (string) $row['status'],
            $this->normalizeDate((string) $row['last_changed_at'], (int) $row['last_changed_at_offset']),
            (int) $row['version'],
        );
    }

    private function normalizeDate(string $value, int $offset): string
    {
        try {
            $sign = $offset < 0 ? '-' : '+';
            $absolute = abs($offset);
            $timezone = new \DateTimeZone(sprintf('%s%02d:%02d', $sign, intdiv($absolute, 60), $absolute % 60));

            return (new \DateTimeImmutable($value))->setTimezone($timezone)->format('Y-m-d\TH:i:s.uP');
        } catch (Throwable) {
            throw PersistentPropertyIntegrity::invalid('date');
        }
    }

    private function offset(string $value): int
    {
        return intdiv((new \DateTimeImmutable($value))->getOffset(), 60);
    }
}

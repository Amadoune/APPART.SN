<?php

namespace Tests\PostgreSQL\RealEstateCatalog;

use Appart\Modules\RealEstateCatalog\Domain\Exception\ConcurrentPropertyModification;
use Appart\Modules\RealEstateCatalog\Domain\Exception\PropertyIdConflict;
use Appart\Modules\RealEstateCatalog\Domain\Exception\PropertyReferenceConflict;
use Appart\Modules\RealEstateCatalog\Domain\Exception\PropertyViolation;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PersistentPropertyIntegrity;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyRepository;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Contracts\RealEstateCatalog\FakePropertyRegistryHarness;

final class PostgreSqlPropertyRepositoryIntegrationTest extends TestCase
{
    private PostgreSqlPropertyRegistryHarness $harness;

    private PostgreSqlPropertyRepository $repository;

    private PDO $connection;

    private FakePropertyRegistryHarness $fixtures;

    protected function setUp(): void
    {
        $this->harness = new PostgreSqlPropertyRegistryHarness;
        $registry = $this->harness->freshRegistry();
        self::assertInstanceOf(PostgreSqlPropertyRepository::class, $registry);
        $this->repository = $registry;
        $this->connection = $this->harness->independentConnection();
        $this->fixtures = new FakePropertyRegistryHarness;
    }

    public function test_add_find_and_save_preserve_complete_root_address_dates_and_events(): void
    {
        $property = $this->fixtures->minimalProperty();
        $this->repository->add($property);
        self::assertNotEmpty($property->releaseEvents());
        $loaded = $this->repository->find($property->id());
        self::assertNotNull($loaded);
        self::assertEquals($property->address(), $loaded->address());
        self::assertEquals($property->lastChangedAt(), $loaded->lastChangedAt());
        $this->fixtures->mutate($loaded);
        $this->repository->save($loaded, 0);

        self::assertSame(1, $this->countRows('real_estate_catalog.properties'));
        self::assertSame(1, $this->countRows('real_estate_catalog.property_addresses'));
        self::assertSame(1, $this->countRows('real_estate_catalog.property_reference_reservations'));
        self::assertNotEmpty($loaded->releaseEvents());
        self::assertSame([], $this->repository->find($property->id())?->releaseEvents());
    }

    public function test_identity_and_reference_conflicts_are_distinct_and_leave_no_partial_root(): void
    {
        $this->repository->add($this->fixtures->minimalProperty());
        try {
            $this->repository->add($this->fixtures->minimalProperty());
            self::fail('PropertyId conflict expected.');
        } catch (PropertyIdConflict) {
            self::assertSame(1, $this->countRows('real_estate_catalog.properties'));
        }
        try {
            $this->repository->add($this->fixtures->minimalProperty($this->fixtures->distinctId(), $this->fixtures->primaryReference()));
            self::fail('PropertyReference conflict expected.');
        } catch (PropertyReferenceConflict) {
            self::assertSame(1, $this->countRows('real_estate_catalog.properties'));
            self::assertSame(1, $this->countRows('real_estate_catalog.property_addresses'));
        }
    }

    public function test_controlled_add_failure_rolls_back_root_reference_and_address(): void
    {
        $property = $this->fixtures->minimalProperty();
        $this->harness->failNextWrite($this->repository);
        try {
            $this->repository->add($property);
            self::fail('Controlled add failure expected.');
        } catch (PersistentPropertyIntegrity) {
            self::assertSame(0, $this->countRows('real_estate_catalog.properties'));
            self::assertSame(0, $this->countRows('real_estate_catalog.property_reference_reservations'));
            self::assertSame(0, $this->countRows('real_estate_catalog.property_addresses'));
        }
    }

    public function test_controlled_save_failure_rolls_back_root_and_address(): void
    {
        $property = $this->fixtures->minimalProperty();
        $this->repository->add($property);
        $loaded = $this->repository->find($property->id());
        self::assertNotNull($loaded);
        $this->fixtures->mutate($loaded);
        $this->harness->failNextWrite($this->repository);
        try {
            $this->repository->save($loaded, 0);
            self::fail('Controlled save failure expected.');
        } catch (ConcurrentPropertyModification) {
            self::assertSame(0, $this->repository->find($property->id())?->version());
            self::assertSame(120, $this->repository->find($property->id())?->surface()?->squareMeters);
            self::assertSame(1, $this->countRows('real_estate_catalog.property_addresses'));
        }
    }

    public function test_database_enforces_reference_uniqueness_foreign_key_and_structural_checks(): void
    {
        $this->repository->add($this->fixtures->minimalProperty());
        foreach ([
            "INSERT INTO real_estate_catalog.property_reference_reservations (reference, property_id) VALUES ('PROP-CONTRACT-001', '33000000-0000-4000-8000-000000000999')",
            "INSERT INTO real_estate_catalog.property_addresses (property_id, address_id, geographic_place_id, address_line) VALUES ('33000000-0000-4000-8000-000000000999', '33000000-0000-4000-8000-000000000199', 'place:dakar', 'Invalid orphan')",
            'UPDATE real_estate_catalog.properties SET version = -1',
        ] as $sql) {
            try {
                $this->connection->exec($sql);
                self::fail('A stable physical constraint must reject the mutation.');
            } catch (PDOException) {
                self::assertSame(1, $this->countRows('real_estate_catalog.properties'));
            }
        }
    }

    public function test_archived_property_reconstructs_terminal_and_keeps_both_reservations(): void
    {
        $archived = $this->fixtures->archivedProperty();
        $this->repository->add($archived);
        $loaded = $this->repository->find($archived->id());
        self::assertNotNull($loaded);
        self::assertSame('archived', $loaded->status()->value);
        try {
            $this->fixtures->mutate($loaded);
            self::fail('Archived must remain terminal.');
        } catch (PropertyViolation) {
            self::assertSame(3, $loaded->version());
        }
        self::assertSame(1, $this->countRows('real_estate_catalog.property_reference_reservations'));
    }

    private function countRows(string $table): int
    {
        return (int) $this->connection->query('SELECT count(*) FROM '.$table)->fetchColumn();
    }
}

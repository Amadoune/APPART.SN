<?php

namespace Tests\PostgreSQL\GeographyPlacePersistence;

use Appart\Modules\Geography\Domain\Exception\ConcurrentPlaceModification;
use Appart\Modules\Geography\Domain\Exception\DuplicatePlaceCode;
use Appart\Modules\Geography\Domain\Exception\DuplicatePlaceId;
use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\Coordinates;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use Appart\Modules\Geography\Infrastructure\Persistence\PlaceMapper;
use Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql\PostgreSqlPlaceRepository;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlPlaceRepositoryTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlPlaceRepository $repository;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->repository = new PostgreSqlPlaceRepository($this->connection, new PlaceMapper);
    }

    public function test_add_find_parent_coordinates_alias_and_optimistic_save(): void
    {
        $country = $this->country('20000000-0000-4000-8000-000000000001', 'Sénégal', 'SN');
        $this->repository->add($country);
        $region = Place::create($this->id('20000000-0000-4000-8000-000000000002'), PlaceName::fromString('Dakar'), PlaceCode::fromString('DKR'), PlaceType::Region, CountryCode::fromString('SN'), $this->at(), $country, Coordinates::fromDecimal(14.7167, -17.4677));
        $this->repository->add($region);
        $region->rename(PlaceName::fromString('Région de Dakar'), $this->at('+1 second'));
        $this->repository->save($region, 1);

        $copy = $this->repository->find($region->id());
        self::assertNotNull($copy);
        self::assertSame('Région de Dakar', $copy->officialName()->value);
        self::assertSame($country->id()->value, $copy->parent()?->placeId->value);
        self::assertSame(14.7167, $copy->coordinates()?->latitude);
        self::assertCount(1, $copy->aliases());
        self::assertSame(2, $copy->version());
    }

    public function test_merge_is_reconstructed_without_redirection(): void
    {
        $target = $this->country('20000000-0000-4000-8000-000000000003', 'Sénégal', 'SN1');
        $source = $this->country('20000000-0000-4000-8000-000000000004', 'Ancien Sénégal', 'SN2');
        $this->repository->add($target);
        $this->repository->add($source);
        $source->mergeInto($target, $this->at('+1 second'));
        $this->repository->save($source, 1);

        $copy = $this->repository->find($source->id());
        self::assertNotNull($copy);
        self::assertFalse($copy->isEnabled());
        self::assertSame($target->id()->value, $copy->mergedInto()?->value);
    }

    public function test_duplicate_id_and_country_code_are_closed(): void
    {
        $first = $this->country('20000000-0000-4000-8000-000000000005', 'Sénégal', 'DUP');
        $this->repository->add($first);
        try {
            $this->repository->add($first);
            self::fail('Duplicate id expected.');
        } catch (DuplicatePlaceId $error) {
            self::assertInstanceOf(DuplicatePlaceId::class, $error);
        }
        $this->expectException(DuplicatePlaceCode::class);
        $this->repository->add($this->country('20000000-0000-4000-8000-000000000006', 'Autre', 'DUP'));
    }

    public function test_version_conflict_writes_nothing(): void
    {
        $place = $this->country('20000000-0000-4000-8000-000000000007', 'Sénégal', 'LOCK');
        $this->repository->add($place);
        $place->rename(PlaceName::fromString('République du Sénégal'), $this->at('+1 second'));
        $this->expectException(ConcurrentPlaceModification::class);
        $this->repository->save($place, 0);
    }

    public function test_migration_down_and_reapplication_preserve_historical_tables(): void
    {
        $down = file_get_contents(dirname(__DIR__, 3).'/src/Modules/Geography/Infrastructure/Persistence/PostgreSql/Migrations/098_geography_places.down.sql');
        $up = file_get_contents(dirname(__DIR__, 3).'/src/Modules/Geography/Infrastructure/Persistence/PostgreSql/Migrations/098_geography_places.sql');
        self::assertIsString($down);
        self::assertIsString($up);
        $this->connection->exec($down);
        self::assertNotFalse($this->connection->query("SELECT to_regclass('geography.place_lifecycle_transitions')::text")->fetchColumn());
        self::assertNotFalse($this->connection->query("SELECT to_regclass('public_geography.decisions')::text")->fetchColumn());
        $this->connection->exec($up);
        self::assertNotFalse($this->connection->query("SELECT to_regclass('geography.places')::text")->fetchColumn());
    }

    private function country(string $id, string $name, string $code): Place
    {
        return Place::create($this->id($id), PlaceName::fromString($name), PlaceCode::fromString($code), PlaceType::Country, CountryCode::fromString('SN'), $this->at());
    }

    private function id(string $id): PlaceId
    {
        return PlaceId::fromString($id);
    }

    private function at(string $modifier = ''): DateTimeImmutable
    {
        $at = new DateTimeImmutable('2026-08-13T10:00:00+00:00');

        return $modifier === '' ? $at : $at->modify($modifier);
    }
}

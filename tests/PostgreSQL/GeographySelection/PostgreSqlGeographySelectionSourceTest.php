<?php

namespace Tests\PostgreSQL\GeographySelection;

use Appart\Modules\Geography\Application\GeographySelection\DeterministicGeographySelectionReader;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionQuery;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionStatus;
use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use Appart\Modules\Geography\Infrastructure\Persistence\PlaceMapper;
use Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql\PostgreSqlGeographySelectionSource;
use Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql\PostgreSqlPlaceRepository;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlGeographySelectionSourceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlPlaceRepository $repository;

    private DeterministicGeographySelectionReader $reader;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->repository = new PostgreSqlPlaceRepository($this->connection, new PlaceMapper);
        $this->reader = new DeterministicGeographySelectionReader(new PostgreSqlGeographySelectionSource($this->connection));
    }

    public function test_keyset_pagination_is_stable_and_excludes_disabled_and_merged_places(): void
    {
        $country = $this->place('40000000-0000-4000-8000-000000000001', 'Sénégal', 'SN', PlaceType::Country);
        $this->repository->add($country);
        $dakar = $this->place('40000000-0000-4000-8000-000000000002', 'Dakar', 'DKR', PlaceType::Region, $country);
        $thies = $this->place('40000000-0000-4000-8000-000000000003', 'Thiès', 'THS', PlaceType::Region, $country);
        $disabled = $this->place('40000000-0000-4000-8000-000000000004', 'Ziguinchor', 'ZIG', PlaceType::Region, $country);
        $merged = $this->place('40000000-0000-4000-8000-000000000005', 'Ancienne Dakar', 'ADK', PlaceType::Region, $country);
        foreach ([$dakar, $thies, $disabled, $merged] as $place) {
            $this->repository->add($place);
        }
        $disabled->disable($this->at('+1 second'));
        $this->repository->save($disabled, 1);
        $merged->mergeInto($dakar, $this->at('+1 second'));
        $this->repository->save($merged, 1);

        $first = $this->reader->read(new GeographySelectionQuery(PlaceType::Region, $country->id(), null, 1));
        self::assertSame(['Dakar'], array_column($first->items, 'label'));
        self::assertNotNull($first->nextCursor);
        $second = $this->reader->read(new GeographySelectionQuery(PlaceType::Region, $country->id(), $first->nextCursor, 1));
        self::assertSame(['Thiès'], array_column($second->items, 'label'));
        self::assertNull($second->nextCursor);
    }

    public function test_root_empty_missing_and_invalid_hierarchy_are_closed(): void
    {
        self::assertSame(GeographySelectionStatus::Empty, $this->reader->read(new GeographySelectionQuery(PlaceType::Country, null, null, 10))->status);

        $missing = $this->id('40000000-0000-4000-8000-000000000099');
        self::assertSame(GeographySelectionStatus::Missing, $this->reader->read(new GeographySelectionQuery(PlaceType::City, $missing, null, 10))->status);

        $country = $this->place('40000000-0000-4000-8000-000000000001', 'Sénégal', 'SN', PlaceType::Country);
        $this->repository->add($country);
        self::assertSame(GeographySelectionStatus::Corrupted, $this->reader->read(new GeographySelectionQuery(PlaceType::Neighborhood, $country->id(), null, 10))->status);
    }

    private function place(string $id, string $name, string $code, PlaceType $type, ?Place $parent = null): Place
    {
        return Place::create($this->id($id), PlaceName::fromString($name), PlaceCode::fromString($code), $type, CountryCode::fromString('SN'), $this->at(), $parent);
    }

    private function id(string $id): PlaceId
    {
        return PlaceId::fromString($id);
    }

    private function at(string $modifier = ''): DateTimeImmutable
    {
        $at = new DateTimeImmutable('2026-08-14T10:00:00+00:00');

        return $modifier === '' ? $at : $at->modify($modifier);
    }
}

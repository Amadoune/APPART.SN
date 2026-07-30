<?php

namespace Tests\Unit\Modules\Geography;

use Appart\Modules\Geography\Application\UseCase\CreatePlace;
use Appart\Modules\Geography\Application\UseCase\DisablePlace;
use Appart\Modules\Geography\Application\UseCase\EnablePlace;
use Appart\Modules\Geography\Application\UseCase\MergePlace;
use Appart\Modules\Geography\Application\UseCase\RenamePlace;
use Appart\Modules\Geography\Domain\Exception\ConcurrentPlaceModification;
use Appart\Modules\Geography\Domain\Exception\DuplicatePlaceCode;
use Appart\Modules\Geography\Domain\Exception\DuplicatePlaceId;
use Appart\Modules\Geography\Domain\Exception\InvalidPlaceHierarchy;
use Appart\Modules\Geography\Domain\Exception\PlaceNotFound;
use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\Service\PlaceHierarchy;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Modules\Geography\Support\FakePlaceRegistry;

final class GeographyUseCasesTest extends TestCase
{
    public function test_create_place_registers_a_new_place_with_its_verified_parent(): void
    {
        $registry = new FakePlaceRegistry;
        $place = $this->createCity($registry);

        self::assertTrue($place->id()->equals($registry->find($place->id())?->id() ?? throw new \RuntimeException));
        $parent = $place->parent();
        self::assertNotNull($parent);
        self::assertSame($this->regionId()->value, $parent->placeId->value);
        self::assertSame(PlaceType::Region, $parent->placeType);
    }

    public function test_create_place_enforces_identifier_uniqueness_atomically(): void
    {
        $registry = new FakePlaceRegistry;
        $this->createCity($registry);

        $this->expectException(DuplicatePlaceId::class);

        $this->createCity($registry);
    }

    public function test_create_place_enforces_official_code_uniqueness_atomically_within_a_country(): void
    {
        $registry = new FakePlaceRegistry;
        $this->createCity($registry);

        $this->expectException(DuplicatePlaceCode::class);

        $this->createCity($registry, '00000000-0000-4000-8000-000000000004', 'Autre Dakar', 'DKR');
    }

    public function test_the_same_official_code_can_exist_in_different_countries(): void
    {
        $registry = new FakePlaceRegistry;
        $this->createCountry($registry);
        $this->createRegion($registry, 'SN', $this->countryId(), $this->regionId(), 'Dakar', 'REG');
        $gambia = $this->createCountry($registry, '00000000-0000-4000-8000-000000000011', 'Gambie', 'GM');
        $region = $this->createRegion(
            $registry,
            'GM',
            $gambia->id(),
            PlaceId::fromString('00000000-0000-4000-8000-000000000012'),
            'Banjul',
            'REG',
        );

        self::assertSame('REG', $region->code()->value);
    }

    public function test_creation_rejects_an_unknown_parent(): void
    {
        $registry = new FakePlaceRegistry;

        $this->expectException(InvalidPlaceHierarchy::class);

        $this->executeCityCreation($registry, PlaceId::fromString('00000000-0000-4000-8000-000000000099'));
    }

    public function test_creation_rejects_a_parent_with_an_incompatible_real_type(): void
    {
        $registry = new FakePlaceRegistry;
        $country = $this->createCountry($registry);

        $this->expectException(InvalidPlaceHierarchy::class);

        $this->executeCityCreation($registry, $country->id());
    }

    public function test_creation_rejects_a_disabled_parent(): void
    {
        $registry = new FakePlaceRegistry;
        $region = $this->seedSenegalHierarchy($registry);
        (new DisablePlace($registry))->execute($region->id(), $this->when());

        $this->expectException(InvalidPlaceHierarchy::class);

        $this->executeCityCreation($registry, $region->id());
    }

    public function test_creation_rejects_a_merged_parent(): void
    {
        $registry = new FakePlaceRegistry;
        $source = $this->seedSenegalHierarchy($registry);
        $target = $this->createRegion(
            $registry,
            'SN',
            $this->countryId(),
            PlaceId::fromString('00000000-0000-4000-8000-000000000005'),
            'Thiès',
            'THR',
        );
        (new MergePlace($registry))->execute($source->id(), $target->id(), $this->when());

        $this->expectException(InvalidPlaceHierarchy::class);

        $this->executeCityCreation($registry, $source->id());
    }

    public function test_creation_rejects_a_parent_from_another_country(): void
    {
        $registry = new FakePlaceRegistry;
        $gambia = $this->createCountry($registry, '00000000-0000-4000-8000-000000000011', 'Gambie', 'GM');
        $banjul = $this->createRegion(
            $registry,
            'GM',
            $gambia->id(),
            PlaceId::fromString('00000000-0000-4000-8000-000000000012'),
            'Banjul',
            'BJL',
        );

        $this->expectException(InvalidPlaceHierarchy::class);

        $this->executeCityCreation($registry, $banjul->id());
    }

    public function test_hierarchy_rejects_direct_self_parenting(): void
    {
        $registry = new FakePlaceRegistry;
        $id = PlaceId::fromString('00000000-0000-4000-8000-000000000003');

        $this->expectException(InvalidPlaceHierarchy::class);

        (new PlaceHierarchy($registry))->verifiedParent($id, $id);
    }

    public function test_hierarchy_rejects_an_indirect_cycle(): void
    {
        $registry = new FakePlaceRegistry;
        $this->seedSenegalHierarchy($registry);

        $this->expectException(InvalidPlaceHierarchy::class);

        (new PlaceHierarchy($registry))->verifiedParent($this->countryId(), $this->regionId());
    }

    public function test_rename_place_updates_the_registered_aggregate(): void
    {
        $registry = new FakePlaceRegistry;
        $place = $this->createCity($registry);

        (new RenamePlace($registry))->execute($place->id(), PlaceName::fromString('Ville de Dakar'), $this->when());

        self::assertSame('Ville de Dakar', $registry->find($place->id())?->officialName()->value);
    }

    public function test_disable_and_enable_place_update_the_registered_aggregate(): void
    {
        $registry = new FakePlaceRegistry;
        $place = $this->createCity($registry);

        (new DisablePlace($registry))->execute($place->id(), $this->when());
        self::assertFalse($registry->find($place->id())?->isEnabled());

        (new EnablePlace($registry))->execute($place->id(), $this->when());
        self::assertTrue($registry->find($place->id())->isEnabled());
    }

    public function test_merge_place_records_the_official_target(): void
    {
        $registry = new FakePlaceRegistry;
        $source = $this->createCity($registry);
        $target = $this->createCity($registry, '00000000-0000-4000-8000-000000000004', 'Grand Dakar', 'GDK');

        (new MergePlace($registry))->execute($source->id(), $target->id(), $this->when());

        self::assertTrue($registry->find($source->id())?->mergedInto()?->equals($target->id()));
    }

    public function test_a_use_case_reports_an_unknown_place(): void
    {
        $registry = new FakePlaceRegistry;

        $this->expectException(PlaceNotFound::class);

        (new DisablePlace($registry))->execute(PlaceId::fromString('00000000-0000-4000-8000-000000000099'), $this->when());
    }

    public function test_registry_rejects_a_stale_detached_aggregate(): void
    {
        $registry = new FakePlaceRegistry;
        $place = $this->createCity($registry);
        $first = $registry->find($place->id()) ?? throw new \RuntimeException;
        $stale = $registry->find($place->id());

        $first->rename(PlaceName::fromString('Dakar A'), $this->when());
        $registry->save($first, 1);
        $stale->rename(PlaceName::fromString('Dakar B'), $this->when());

        $this->expectException(ConcurrentPlaceModification::class);
        $registry->save($stale, 1);
    }

    private function createCity(
        FakePlaceRegistry $registry,
        string $id = '00000000-0000-4000-8000-000000000003',
        string $name = 'Dakar',
        string $code = 'DKR',
    ): Place {
        $region = $this->seedSenegalHierarchy($registry);

        return $this->executeCityCreation($registry, $region->id(), $id, $name, $code);
    }

    private function executeCityCreation(
        FakePlaceRegistry $registry,
        PlaceId $parentId,
        string $id = '00000000-0000-4000-8000-000000000003',
        string $name = 'Dakar',
        string $code = 'DKR',
    ): Place {
        return (new CreatePlace($registry))->execute(
            PlaceId::fromString($id),
            PlaceName::fromString($name),
            PlaceCode::fromString($code),
            PlaceType::City,
            CountryCode::fromString('SN'),
            $this->when(),
            $parentId,
        );
    }

    private function seedSenegalHierarchy(FakePlaceRegistry $registry): Place
    {
        $country = $registry->find($this->countryId()) ?? $this->createCountry($registry);

        return $registry->find($this->regionId()) ?? $this->createRegion(
            $registry,
            'SN',
            $country->id(),
            $this->regionId(),
            'Dakar',
            'DKR-REG',
        );
    }

    private function createCountry(
        FakePlaceRegistry $registry,
        string $id = '00000000-0000-4000-8000-000000000001',
        string $name = 'Sénégal',
        string $countryCode = 'SN',
    ): Place {
        return (new CreatePlace($registry))->execute(
            PlaceId::fromString($id),
            PlaceName::fromString($name),
            PlaceCode::fromString($countryCode),
            PlaceType::Country,
            CountryCode::fromString($countryCode),
            $this->when(),
        );
    }

    private function createRegion(
        FakePlaceRegistry $registry,
        string $countryCode,
        PlaceId $countryId,
        PlaceId $regionId,
        string $name,
        string $code,
    ): Place {
        return (new CreatePlace($registry))->execute(
            $regionId,
            PlaceName::fromString($name),
            PlaceCode::fromString($code),
            PlaceType::Region,
            CountryCode::fromString($countryCode),
            $this->when(),
            $countryId,
        );
    }

    private function countryId(): PlaceId
    {
        return PlaceId::fromString('00000000-0000-4000-8000-000000000001');
    }

    private function regionId(): PlaceId
    {
        return PlaceId::fromString('00000000-0000-4000-8000-000000000002');
    }

    private function when(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-16T12:00:00+00:00');
    }
}

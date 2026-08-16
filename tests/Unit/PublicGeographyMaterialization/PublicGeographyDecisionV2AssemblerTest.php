<?php

namespace Tests\Unit\PublicGeographyMaterialization;

use App\Application\PublicGeographyMaterialization\PublicGeographyDecisionV2Assembler;
use App\Application\PublicGeographyMaterialization\PublicGeographyHierarchy;
use App\Application\PublicGeographyRevision\PublicGeographyRevisionStrategy;
use App\Application\PublicGeographySource\PublicGeographyDecisionStatusV2;
use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class PublicGeographyDecisionV2AssemblerTest extends TestCase
{
    public function test_available_hierarchy_is_root_to_leaf_without_url_and_with_sum_watermark(): void
    {
        [$country, $region, $city] = $this->hierarchy();

        $decision = (new PublicGeographyDecisionV2Assembler(new PublicGeographyRevisionStrategy))->assemble(new PublicGeographyHierarchy([$country, $region, $city]), 'listing:test');

        self::assertSame(PublicGeographyDecisionStatusV2::Available, $decision->status);
        self::assertSame('c3120000-0000-4000-8000-000000000003', $decision->terminalPlaceId);
        self::assertSame('Dakar', $decision->locality);
        self::assertSame(['Senegal', 'Dakar Region', 'Dakar'], array_map(static fn ($item): string => $item->officialName, $decision->breadcrumb));
        self::assertSame([1, 1, 1], array_map(static fn ($item): int => $item->aggregateVersion, $decision->revisionVector));
        self::assertSame(3, $decision->revision->watermarkVersion());
        self::assertStringNotContainsString('url', strtolower($decision->canonicalPayload()));
    }

    public function test_disabled_member_produces_durable_unavailable_with_vector(): void
    {
        [$country, $region, $city] = $this->hierarchy();
        $region->disable(new DateTimeImmutable('2026-08-15T10:01:00Z'));

        $decision = (new PublicGeographyDecisionV2Assembler(new PublicGeographyRevisionStrategy))->assemble(new PublicGeographyHierarchy([$country, $region, $city]), 'mutation:test');

        self::assertSame(PublicGeographyDecisionStatusV2::Unavailable, $decision->status);
        self::assertNull($decision->locality);
        self::assertSame([], $decision->breadcrumb);
        self::assertSame([1, 2, 1], array_map(static fn ($item): int => $item->aggregateVersion, $decision->revisionVector));
        self::assertSame(4, $decision->revision->watermarkVersion());
    }

    /** @return array{Place,Place,Place} */
    private function hierarchy(): array
    {
        $at = new DateTimeImmutable('2026-08-15T10:00:00Z');
        $country = Place::create(PlaceId::fromString('c3120000-0000-4000-8000-000000000001'), PlaceName::fromString('Senegal'), PlaceCode::fromString('SN'), PlaceType::Country, CountryCode::fromString('SN'), $at);
        $region = Place::create(PlaceId::fromString('c3120000-0000-4000-8000-000000000002'), PlaceName::fromString('Dakar Region'), PlaceCode::fromString('DKR'), PlaceType::Region, CountryCode::fromString('SN'), $at, $country);
        $city = Place::create(PlaceId::fromString('c3120000-0000-4000-8000-000000000003'), PlaceName::fromString('Dakar'), PlaceCode::fromString('DAK'), PlaceType::City, CountryCode::fromString('SN'), $at, $region);

        return [$country, $region, $city];
    }
}

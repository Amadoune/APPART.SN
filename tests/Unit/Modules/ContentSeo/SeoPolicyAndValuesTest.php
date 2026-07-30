<?php

namespace Tests\Unit\Modules\ContentSeo;

use Appart\Modules\ContentSeo\Domain\Exception\InconsistentSeoSources;
use Appart\Modules\ContentSeo\Domain\Exception\InvalidSeoValue;
use Appart\Modules\ContentSeo\Domain\Exception\SeoViolation;
use Appart\Modules\ContentSeo\Domain\Model\SeoMaterial;
use Appart\Modules\ContentSeo\Domain\Model\StructuredData;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\MetaDescription;
use Appart\Modules\ContentSeo\Domain\ValueObject\PropertySeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\RobotsPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\SearchSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoTitle;
use Appart\Modules\ContentSeo\Domain\ValueObject\SitemapPriority;
use Appart\Modules\ContentSeo\Domain\ValueObject\StructuredDataType;

final class SeoPolicyAndValuesTest extends ContentSeoTestCase
{
    public function test_visibility_requires_all_three_public_sources(): void
    {
        self::assertSame(SeoProjectionState::Removed, $this->material($this->sources(1, searchState: SearchSeoState::Hidden))->state);
        self::assertSame(SeoProjectionState::Removed, $this->material($this->sources(1, propertyState: PropertySeoState::Unavailable))->state);
    }

    public function test_non_public_projection_has_coherent_robots_and_sitemap(): void
    {
        $material = $this->material($this->sources(1, ListingSeoState::NotPublished));
        self::assertSame(RobotsPolicy::NoIndexFollow, $material->robots);
        self::assertFalse($material->inSitemap);
        self::assertSame(0, $material->sitemapPriority->percent);
    }

    public function test_mismatched_source_identities_are_rejected(): void
    {
        [$listing, $search] = $this->sources(1);
        [, , $property] = $this->sources(1, id: $this->listingId(2));
        $this->expectException(InconsistentSeoSources::class);
        $this->material([$listing, $search, $property]);
    }

    public function test_canonical_is_https_scoped_and_normalized(): void
    {
        self::assertSame('https://appart.sn/annonces/test', CanonicalUrl::fromString('https://www.appart.sn/annonces/test/')->value);
        $this->expectException(InvalidSeoValue::class);
        CanonicalUrl::fromString('https://example.com/annonces/test');
    }

    public function test_title_and_description_enforce_quality_bounds(): void
    {
        self::assertNotSame('', SeoTitle::fromString('Appartement à Dakar')->value);
        self::assertNotSame('', MetaDescription::fromString(str_repeat('description utile ', 4))->value);
        $this->expectException(InvalidSeoValue::class);
        SeoTitle::fromString('Court');
    }

    public function test_sitemap_priority_is_bounded(): void
    {
        $this->expectException(InvalidSeoValue::class);
        SitemapPriority::fromPercent(101);
    }

    public function test_structured_data_rejects_private_or_unknown_facts(): void
    {
        $this->expectException(InvalidSeoValue::class);
        new StructuredData(StructuredDataType::RealEstateListing, ['ownerEmail' => 'private@example.test']);
    }

    public function test_structured_data_requires_every_property(): void
    {
        $this->expectException(InvalidSeoValue::class);
        new StructuredData(StructuredDataType::RealEstateListing, ['name' => 'Incomplete']);
    }

    public function test_structured_data_has_deterministic_property_order(): void
    {
        $data = new StructuredData(StructuredDataType::RealEstateListing, [
            'url' => 'https://appart.sn/annonces/test',
            'name' => 'Appartement test | APPART.SN',
            'category' => 'Appartement',
            'addressLocality' => 'Dakar',
        ]);
        self::assertSame(['addressLocality', 'category', 'name', 'url'], array_keys($data->facts));
    }

    public function test_inconsistent_material_cannot_be_constructed_outside_use_cases(): void
    {
        $valid = $this->material($this->sources(1));
        $this->expectException(SeoViolation::class);
        SeoMaterial::derived(SeoProjectionState::Active, $valid->title, $valid->description, $valid->canonical, RobotsPolicy::NoIndexFollow, $valid->structuredData, true, SitemapPriority::fromPercent(70), $valid->revisions);
    }

    public function test_equivalent_canonical_paths_normalize_to_one_value(): void
    {
        $expected = 'https://appart.sn/annonces/appartement-dakar';
        self::assertSame($expected, CanonicalUrl::fromString('https://APPART.SN//ANNONCES/./VILLA/../Appartement-Dakar/')->value);
        self::assertSame($expected, CanonicalUrl::fromString('https://www.appart.sn/annonces/%61ppartement-dakar')->value);
    }
}

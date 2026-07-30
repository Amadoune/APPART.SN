<?php

namespace Tests\Unit\PublicProjectionStore;

use App\Application\PublicProjectionStore\PublicProjectionPromotionReadiness;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use App\Application\PublicProjectionStore\PublicProjectionWatermarkRelation;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PublicProjectionWatermarkTest extends TestCase
{
    public function test_identical_vectors_are_equal(): void
    {
        self::assertSame(PublicProjectionWatermarkRelation::Equal, $this->watermark()->compareTo($this->watermark()));
    }

    public function test_componentwise_greater_vector_is_newer(): void
    {
        self::assertSame(PublicProjectionWatermarkRelation::Newer, $this->watermark(2, 2, 2, 2, 2, 2, 2)->compareTo($this->watermark()));
    }

    public function test_componentwise_lower_vector_is_older(): void
    {
        self::assertSame(PublicProjectionWatermarkRelation::Older, $this->watermark()->compareTo($this->watermark(2, 2, 2, 2, 2, 2, 2)));
    }

    public function test_crossed_components_are_incomparable_without_last_write_wins(): void
    {
        self::assertSame(PublicProjectionWatermarkRelation::Incomparable, $this->watermark(listing: 2, property: 1)->compareTo($this->watermark(listing: 1, property: 2)));
    }

    public function test_public_geography_without_stable_version_blocks_promotion(): void
    {
        $watermark = $this->watermark(geography: null);

        self::assertSame(PublicProjectionPromotionReadiness::MissingPublicGeographyVersion, $watermark->readiness());
        self::assertSame(PublicProjectionWatermarkRelation::Incomplete, $watermark->compareTo($this->watermark()));
    }

    public function test_public_media_without_stable_version_blocks_promotion(): void
    {
        self::assertSame(PublicProjectionPromotionReadiness::MissingPublicMediaVersion, $this->watermark(publicMedia: null)->readiness());
    }

    public function test_both_unversioned_public_sources_are_explicit(): void
    {
        self::assertSame(PublicProjectionPromotionReadiness::MissingPublicGeographyAndMediaVersions, $this->watermark(geography: null, publicMedia: null)->readiness());
    }

    public function test_watermark_is_immutable_and_contains_no_temporal_ordering_field(): void
    {
        $type = new ReflectionClass(PublicProjectionWatermark::class);

        self::assertTrue($type->isReadOnly());
        self::assertSame([
            'listingVersion',
            'propertyVersion',
            'mediaVersion',
            'searchVersion',
            'contentSeoVersion',
            'publicGeographyVersion',
            'publicMediaVersion',
        ], array_map(static fn ($property): string => $property->getName(), $type->getProperties()));
    }

    private function watermark(
        int $listing = 1,
        int $property = 1,
        int $media = 1,
        int $search = 1,
        int $contentSeo = 1,
        ?int $geography = 1,
        ?int $publicMedia = 1,
    ): PublicProjectionWatermark {
        return new PublicProjectionWatermark($listing, $property, $media, $search, $contentSeo, $geography, $publicMedia);
    }
}

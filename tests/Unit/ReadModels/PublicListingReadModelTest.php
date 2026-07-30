<?php

namespace Tests\Unit\ReadModels;

use App\Projections\SearchListingProjection;
use App\Projections\SeoListingProjection;
use App\ReadModels\InconsistentPublicListingProjection;
use App\ReadModels\PublicListingReadModel;
use App\ReadModels\PublicListingReadModelBuilder;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class PublicListingReadModelTest extends TestCase
{
    private PublicListingReadModelBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new PublicListingReadModelBuilder;
    }

    public function test_complete_indexable_composition_copies_every_search_and_seo_field(): void
    {
        $search = $this->search();
        $seo = $this->seo();
        $model = $this->builder->build($search, $seo);

        self::assertInstanceOf(PublicListingReadModel::class, $model);
        self::assertTrue((new \ReflectionClass($model))->isReadOnly());
        self::assertSame($search->listingId, $model->listingId);
        self::assertSame($search->propertyId, $model->propertyId);
        self::assertSame($search->mediaCollectionId, $model->mediaCollectionId);
        self::assertSame($search->propertyType, $model->propertyType);
        self::assertSame($search->surfaceSquareMeters, $model->surfaceSquareMeters);
        self::assertSame($search->roomCount, $model->roomCount);
        self::assertSame($search->geographicPlaceId, $model->geographicPlaceId);
        self::assertSame($search->primaryMediaId, $model->primaryMediaId);
        self::assertSame($search->listingStatus, $model->listingStatus);
        self::assertSame($search->publishedAt, $model->publishedAt);
        self::assertSame($search->expiresAt, $model->expiresAt);
        self::assertSame($seo->headline, $model->headline);
        self::assertSame($seo->description, $model->description);
        self::assertSame($seo->publicMediaUrl, $model->publicMediaUrl);
        self::assertSame($seo->canonicalUrl, $model->canonicalUrl);
        self::assertSame($seo->canonicalHistory, $model->canonicalHistory);
        self::assertSame($seo->indexability, $model->indexability);
        self::assertSame($seo->robots, $model->robots);
        self::assertSame($seo->htmlRobotsDirective, $model->htmlRobotsDirective);
        self::assertSame($seo->breadcrumb, $model->breadcrumb);
        self::assertSame($seo->structuredData, $model->structuredData);
        self::assertSame($seo->publicJsonLd, $model->publicJsonLd);
        self::assertSame($seo->decidedAt, $model->decidedAt);
        self::assertSame($seo->expiredListingTreatment, $model->expiredListingTreatment);
    }

    public function test_complete_noindex_composition_is_preserved_without_redecision(): void
    {
        $model = $this->builder->build($this->search(), $this->seo(indexability: 'not_indexable', robots: 'noindex_follow', htmlRobotsDirective: 'noindex, follow', publicJsonLd: null));

        self::assertSame('not_indexable', $model?->indexability);
        self::assertSame('noindex_follow', $model?->robots);
    }

    public function test_mismatched_listing_identity_is_rejected(): void
    {
        $this->expectException(InconsistentPublicListingProjection::class);
        $this->builder->build($this->search(), $this->seo(listingId: '91000000-0000-4000-8000-000000000099'));
    }

    public function test_mismatched_publication_date_is_rejected(): void
    {
        $this->expectException(InconsistentPublicListingProjection::class);
        $this->builder->build($this->search(), $this->seo(publishedAt: $this->at(2)));
    }

    public function test_mismatched_expiration_date_is_rejected(): void
    {
        $this->expectException(InconsistentPublicListingProjection::class);
        $this->builder->build($this->search(), $this->seo(expiresAt: $this->at(121)));
    }

    public function test_absent_search_projection_produces_no_public_read_model(): void
    {
        self::assertNull($this->builder->build(null, $this->seo()));
    }

    public function test_absent_seo_projection_produces_no_public_read_model(): void
    {
        self::assertNull($this->builder->build($this->search(), null));
    }

    public function test_reconstruction_is_deterministic_and_sources_are_not_mutated(): void
    {
        $search = $this->search();
        $seo = $this->seo();
        $searchBefore = serialize($search);
        $seoBefore = serialize($seo);

        $first = $this->builder->build($search, $seo);
        $second = $this->builder->build($search, $seo);

        self::assertEquals($first, $second);
        self::assertSame($searchBefore, serialize($search));
        self::assertSame($seoBefore, serialize($seo));
        self::assertSame($seo->decidedAt, $first?->decidedAt, 'No timestamp may be generated.');
    }

    public function test_canonical_breadcrumb_structured_data_and_media_url_are_exact_copies(): void
    {
        $seo = $this->seo();
        $model = $this->builder->build($this->search(), $seo);

        self::assertSame($seo->canonicalUrl, $model?->canonicalUrl);
        self::assertSame($seo->canonicalHistory, $model?->canonicalHistory);
        self::assertSame($seo->breadcrumb, $model?->breadcrumb);
        self::assertSame($seo->structuredData, $model?->structuredData);
        self::assertSame($seo->publicMediaUrl, $model?->publicMediaUrl);
    }

    public function test_structurally_impossible_public_states_are_rejected(): void
    {
        $this->expectException(InconsistentPublicListingProjection::class);
        $this->builder->build($this->search(), $this->seo(indexability: 'indexable', robots: 'noindex_follow'));
    }

    private function search(): SearchListingProjection
    {
        return new SearchListingProjection(
            listingId: $this->listingId(),
            propertyId: '92000000-0000-4000-8000-000000000001',
            mediaCollectionId: '93000000-0000-4000-8000-000000000001',
            listingStatus: 'published',
            geographicPlaceId: 'place:dakar:plateau',
            propertyType: 'apartment',
            surfaceSquareMeters: 120,
            roomCount: 5,
            primaryMediaId: '93000000-0000-4000-8000-000000000101',
            publishedAt: $this->at(1),
            expiresAt: $this->at(120),
        );
    }

    private function seo(
        ?string $listingId = null,
        string $indexability = 'indexable',
        string $robots = 'index_follow',
        string $htmlRobotsDirective = 'index, follow',
        ?string $publicJsonLd = '{"@context":"https://schema.org","@type":"RealEstateListing","name":"Appartement moderne a Dakar | APPART.SN","url":"https://appart.sn/annonces/appartement-moderne-dakar","category":"Appartement","addressLocality":"Dakar","image":"https://media.appart.sn/listings/primary.webp"}',
        ?DateTimeImmutable $publishedAt = null,
        ?DateTimeImmutable $expiresAt = null,
    ): SeoListingProjection {
        return new SeoListingProjection(
            listingId: $listingId ?? $this->listingId(),
            canonicalUrl: 'https://appart.sn/annonces/appartement-moderne-dakar',
            canonicalHistory: [[
                'url' => 'https://appart.sn/annonces/appartement-moderne-dakar',
                'disposition' => 'current',
                'effectiveAt' => $this->at(1),
                'replacedAt' => null,
                'redirectTarget' => null,
            ]],
            headline: 'Appartement moderne a Dakar | APPART.SN',
            description: 'Decouvrez cet appartement moderne, lumineux et bien situe au coeur de Dakar pour votre prochain logement.',
            indexability: $indexability,
            robots: $robots,
            htmlRobotsDirective: $htmlRobotsDirective,
            breadcrumb: [
                ['label' => 'Accueil', 'url' => 'https://appart.sn/accueil'],
                ['label' => 'Appartement moderne a Dakar', 'url' => 'https://appart.sn/annonces/appartement-moderne-dakar'],
            ],
            structuredData: [
                'type' => 'real_estate_listing',
                'facts' => [
                    'addressLocality' => 'Dakar',
                    'category' => 'Appartement',
                    'name' => 'Appartement moderne a Dakar | APPART.SN',
                    'url' => 'https://appart.sn/annonces/appartement-moderne-dakar',
                ],
            ],
            publicJsonLd: $publicJsonLd,
            publicMediaUrl: 'https://media.appart.sn/listings/primary.webp',
            publishedAt: $publishedAt ?? $this->at(1),
            expiresAt: $expiresAt ?? $this->at(120),
            decidedAt: $this->at(2),
            expiredListingTreatment: 'not_applicable',
        );
    }

    private function listingId(): string
    {
        return '91000000-0000-4000-8000-000000000001';
    }

    private function at(int $minute): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-18T10:00:00+00:00')->modify("+{$minute} minutes");
    }
}

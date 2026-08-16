<?php

namespace Tests\Unit\Projections;

use App\Projections\SeoListingProjection;
use App\Projections\SeoListingProjectionBuilder;
use Appart\Modules\ContentSeo\Domain\Model\BreadcrumbItem;
use Appart\Modules\ContentSeo\Domain\Model\CanonicalHistoryEntry;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoDecision;
use Appart\Modules\ContentSeo\Domain\Model\PublicGeographyBreadcrumbItemV2;
use Appart\Modules\ContentSeo\Domain\Model\PublicJsonLd;
use Appart\Modules\ContentSeo\Domain\Model\StructuredData;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalDisposition;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\ExpiredListingTreatment;
use Appart\Modules\ContentSeo\Domain\ValueObject\HtmlRobotsDirective;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\MetaDescription;
use Appart\Modules\ContentSeo\Domain\ValueObject\PublicMediaUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\RobotsPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoIndexability;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoPageTreatment;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoTitle;
use Appart\Modules\ContentSeo\Domain\ValueObject\StructuredDataType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class SeoListingProjectionTest extends TestCase
{
    private SeoListingProjectionBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new SeoListingProjectionBuilder;
    }

    public function test_indexable_decision_is_copied_into_a_complete_immutable_projection(): void
    {
        $decision = $this->decision();
        $projection = $this->builder->build($decision);

        self::assertInstanceOf(SeoListingProjection::class, $projection);
        self::assertTrue((new \ReflectionClass($projection))->isReadOnly());
        self::assertSame($decision->listingId->value, $projection->listingId);
        self::assertSame($decision->canonical->value, $projection->canonicalUrl);
        self::assertSame($decision->headline?->value, $projection->headline);
        self::assertSame($decision->description?->value, $projection->description);
        self::assertSame($decision->indexability->value, $projection->indexability);
        self::assertSame($decision->robots->value, $projection->robots);
        self::assertSame($decision->htmlRobotsDirective->value, $projection->htmlRobotsDirective);
        self::assertSame($decision->publicJsonLd?->json, $projection->publicJsonLd);
        self::assertSame($decision->publicMedia?->value, $projection->publicMediaUrl);
        self::assertSame($decision->publishedAt, $projection->publishedAt);
        self::assertSame($decision->expiresAt, $projection->expiresAt);
        self::assertSame($decision->decidedAt, $projection->decidedAt);
    }

    public function test_canonical_history_is_copied_exactly_without_recomposition(): void
    {
        $decision = $this->decision();
        $projection = $this->builder->build($decision);

        self::assertSame([
            [
                'url' => 'https://appart.sn/annonces/ancienne-canonical',
                'disposition' => 'reserved_for_redirect',
                'effectiveAt' => $decision->canonicalHistory[0]->effectiveAt,
                'replacedAt' => $decision->canonicalHistory[0]->replacedAt,
                'redirectTarget' => $decision->canonical->value,
            ],
            [
                'url' => $decision->canonical->value,
                'disposition' => 'current',
                'effectiveAt' => $decision->canonicalHistory[1]->effectiveAt,
                'replacedAt' => null,
                'redirectTarget' => null,
            ],
        ], $projection?->canonicalHistory);
    }

    public function test_breadcrumb_and_structured_data_are_copied_exactly(): void
    {
        $decision = $this->decision();
        $projection = $this->builder->build($decision);

        self::assertSame([
            ['label' => 'Accueil', 'url' => 'https://appart.sn/accueil'],
            ['label' => 'Appartement moderne a Dakar', 'url' => $decision->canonical->value],
        ], $projection?->breadcrumb);
        self::assertSame([
            'type' => 'real_estate_listing',
            'facts' => $decision->structuredData?->facts,
        ], $projection?->structuredData);
    }

    public function test_expired_remove_decision_produces_no_projection(): void
    {
        $decision = $this->decision(SeoIndexability::NotIndexable, SeoPageTreatment::Remove, ExpiredListingTreatment::Remove);

        self::assertNull($this->builder->build($decision));
    }

    public function test_expired_retain_decision_produces_a_noindex_projection(): void
    {
        $decision = $this->decision(SeoIndexability::NotIndexable, SeoPageTreatment::Retain, ExpiredListingTreatment::RetainNoIndex);
        $projection = $this->builder->build($decision);

        self::assertSame('not_indexable', $projection?->indexability);
        self::assertSame('noindex_follow', $projection?->robots);
        self::assertSame('retain_noindex', $projection?->expiredListingTreatment);
    }

    public function test_explicitly_retained_non_indexable_decision_produces_a_noindex_projection(): void
    {
        $decision = $this->decision(SeoIndexability::NotIndexable, SeoPageTreatment::Retain);
        $projection = $this->builder->build($decision);

        self::assertInstanceOf(SeoListingProjection::class, $projection);
        self::assertSame('noindex_follow', $projection->robots);
    }

    public function test_reconstruction_is_identical_and_does_not_mutate_the_decision(): void
    {
        $decision = $this->decision();
        $before = serialize($decision);

        $first = $this->builder->build($decision);
        $second = $this->builder->build($decision);

        self::assertEquals($first, $second);
        self::assertSame($before, serialize($decision));
    }

    public function test_builder_generates_no_identity_url_or_timestamp(): void
    {
        $decision = $this->decision();
        $projection = $this->builder->build($decision);

        self::assertSame('90000000-0000-4000-8000-000000000001', $projection?->listingId);
        self::assertSame('https://appart.sn/annonces/decision-canonique', $projection?->canonicalUrl);
        self::assertSame($decision->decidedAt, $projection?->decidedAt);
    }

    public function test_v2_geography_breadcrumb_is_serialized_without_url(): void
    {
        $base = $this->decision();
        $decision = ListingSeoDecision::decide(
            listingId: $base->listingId,
            canonical: $base->canonical,
            canonicalHistory: $base->canonicalHistory,
            headline: $base->headline,
            description: $base->description,
            indexability: $base->indexability,
            robots: $base->robots,
            htmlRobotsDirective: $base->htmlRobotsDirective,
            pageTreatment: $base->pageTreatment,
            breadcrumb: [new BreadcrumbItem('Appartement moderne a Dakar', $base->canonical)],
            structuredData: $base->structuredData,
            publicJsonLd: $base->publicJsonLd,
            publicMedia: $base->publicMedia,
            expiredTreatment: $base->expiredTreatment,
            publishedAt: $base->publishedAt,
            expiresAt: $base->expiresAt,
            decidedAt: $base->decidedAt,
            geographyBreadcrumbV2: [new PublicGeographyBreadcrumbItemV2('city:dakar', 'city', 'Dakar')],
        );

        $projection = $this->builder->build($decision);

        self::assertSame('public-geography-breadcrumb-v2', $projection?->breadcrumbSchemaVersion);
        self::assertSame([['placeId' => 'city:dakar', 'type' => 'city', 'label' => 'Dakar']], $projection?->geographyBreadcrumb);
        self::assertArrayNotHasKey('url', $projection->geographyBreadcrumb[0]);
    }

    private function decision(
        SeoIndexability $indexability = SeoIndexability::Indexable,
        SeoPageTreatment $pageTreatment = SeoPageTreatment::Retain,
        ExpiredListingTreatment $expiredTreatment = ExpiredListingTreatment::NotApplicable,
    ): ListingSeoDecision {
        $canonical = CanonicalUrl::fromString('https://appart.sn/annonces/decision-canonique');
        $title = SeoTitle::fromString('Appartement moderne a Dakar | APPART.SN');
        $description = MetaDescription::fromString('Decouvrez cet appartement moderne, lumineux et bien situe au coeur de Dakar pour votre prochain logement.');
        $history = [
            new CanonicalHistoryEntry(CanonicalUrl::fromString('https://appart.sn/annonces/ancienne-canonical'), CanonicalDisposition::ReservedForRedirect, $this->at(0), $this->at(1), $canonical),
            new CanonicalHistoryEntry($canonical, CanonicalDisposition::Current, $this->at(1)),
        ];
        $breadcrumb = [
            new BreadcrumbItem('Accueil', CanonicalUrl::fromString('https://appart.sn/accueil')),
            new BreadcrumbItem('Appartement moderne a Dakar', $canonical),
        ];
        $structuredData = new StructuredData(StructuredDataType::RealEstateListing, [
            'name' => $title->value,
            'url' => $canonical->value,
            'category' => 'Appartement',
            'addressLocality' => 'Dakar',
        ]);
        $robots = $indexability === SeoIndexability::Indexable ? RobotsPolicy::IndexFollow : RobotsPolicy::NoIndexFollow;
        $media = PublicMediaUrl::fromString('https://media.appart.sn/listings/primary.webp');

        return ListingSeoDecision::decide(
            listingId: ListingId::fromString('90000000-0000-4000-8000-000000000001'),
            canonical: $canonical,
            canonicalHistory: $history,
            headline: $title,
            description: $description,
            indexability: $indexability,
            robots: $robots,
            htmlRobotsDirective: HtmlRobotsDirective::fromPolicy($robots),
            pageTreatment: $pageTreatment,
            breadcrumb: $breadcrumb,
            structuredData: $structuredData,
            publicJsonLd: $indexability === SeoIndexability::Indexable ? PublicJsonLd::fromStructuredData($structuredData, $media) : null,
            publicMedia: $media,
            expiredTreatment: $expiredTreatment,
            publishedAt: $this->at(1),
            expiresAt: $this->at(120),
            decidedAt: $this->at(2),
        );
    }

    private function at(int $minute): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-18T10:00:00+00:00')->modify("+{$minute} minutes");
    }
}

<?php

namespace Tests\Unit\Modules\ContentSeo;

use Appart\Modules\ContentSeo\Domain\Exception\InvalidSeoValue;
use Appart\Modules\ContentSeo\Domain\Model\BreadcrumbItem;
use Appart\Modules\ContentSeo\Domain\Model\CanonicalHistoryEntry;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoDecision;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PublicGeographySeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PublicMediaSeoSource;
use Appart\Modules\ContentSeo\Domain\Policy\CanonicalHistoryPolicy;
use Appart\Modules\ContentSeo\Domain\Policy\CanonicalPolicy;
use Appart\Modules\ContentSeo\Domain\Policy\ListingSeoDecisionPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalDisposition;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\ExpiredListingTreatment;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\PublicMediaUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\RobotsPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoIndexability;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoPageTreatment;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceKind;

final class ListingSeoDecisionPolicyTest extends ContentSeoTestCase
{
    private ListingSeoDecisionPolicy $policy;

    protected function setUp(): void
    {
        $this->policy = new ListingSeoDecisionPolicy(new CanonicalPolicy, new CanonicalHistoryPolicy);
    }

    public function test_complete_public_sources_produce_an_indexable_decision(): void
    {
        $decision = $this->decision();

        self::assertSame(SeoIndexability::Indexable, $decision->indexability);
        self::assertSame(RobotsPolicy::IndexFollow, $decision->robots);
        self::assertSame('index, follow', $decision->htmlRobotsDirective->value);
        self::assertNotNull($decision->publicJsonLd);
        self::assertSame('https://schema.org', $decision->publicJsonLd->document['@context']);
        self::assertSame('RealEstateListing', $decision->publicJsonLd->document['@type']);
        self::assertSame(SeoPageTreatment::Retain, $decision->pageTreatment);
        self::assertSame('https://appart.sn/annonces/appartement-moderne-dakar', $decision->canonical->value);
        self::assertNotNull($decision->headline);
        self::assertNotNull($decision->description);
        self::assertNotNull($decision->structuredData);
        self::assertNotNull($decision->publicMedia);
        self::assertCount(3, $decision->breadcrumb);
        self::assertEquals($this->at(1), $decision->publishedAt);
        self::assertEquals($this->at(120), $decision->expiresAt);
    }

    public function test_non_public_listing_produces_a_coherent_non_indexable_decision(): void
    {
        $decision = $this->decision(state: ListingSeoState::NotPublished);

        self::assertSame(SeoIndexability::NotIndexable, $decision->indexability);
        self::assertSame(RobotsPolicy::NoIndexFollow, $decision->robots);
        self::assertSame('noindex, follow', $decision->htmlRobotsDirective->value);
        self::assertNull($decision->publicJsonLd);
        self::assertSame(SeoPageTreatment::Remove, $decision->pageTreatment);
    }

    public function test_canonical_is_stable_when_the_decided_path_is_unchanged(): void
    {
        $first = $this->decision();
        $second = $this->decision(history: $first->canonicalHistory, atMinute: 3);

        self::assertSame($first->canonical->value, $second->canonical->value);
        self::assertEquals($first->canonicalHistory, $second->canonicalHistory);
    }

    public function test_canonical_change_preserves_history_and_redirects_to_the_current_url(): void
    {
        $first = $this->decision();
        $changed = $this->decision(path: 'annonces/nouvelle-url-publique', history: $first->canonicalHistory, atMinute: 3);

        self::assertCount(2, $changed->canonicalHistory);
        self::assertSame(CanonicalDisposition::ReservedForRedirect, $changed->canonicalHistory[0]->disposition);
        self::assertSame($changed->canonical->value, $changed->canonicalHistory[0]->redirectTarget?->value);
        self::assertSame(CanonicalDisposition::Current, $changed->canonicalHistory[1]->disposition);
    }

    public function test_insufficient_content_is_never_promoted_to_an_indexable_decision(): void
    {
        $decision = $this->decision(headline: 'Court', description: 'Trop court');

        self::assertSame(SeoIndexability::NotIndexable, $decision->indexability);
        self::assertNull($decision->headline);
        self::assertNull($decision->description);
        self::assertSame([], $decision->breadcrumb);
    }

    public function test_missing_public_geography_is_explicit_and_non_indexable(): void
    {
        $decision = $this->decision(geography: false);

        self::assertSame(SeoIndexability::NotIndexable, $decision->indexability);
        self::assertSame([], $decision->breadcrumb);
        self::assertNull($decision->structuredData);
    }

    public function test_missing_public_media_is_explicit_and_non_indexable(): void
    {
        $decision = $this->decision(media: false);

        self::assertSame(SeoIndexability::NotIndexable, $decision->indexability);
        self::assertNull($decision->publicMedia);
    }

    public function test_expired_listing_is_noindex_and_uses_only_the_explicit_retention_directive(): void
    {
        $removed = $this->decision(state: ListingSeoState::Expired, expiredTreatment: ExpiredListingTreatment::Remove);
        $retained = $this->decision(state: ListingSeoState::Expired, expiredTreatment: ExpiredListingTreatment::RetainNoIndex);

        self::assertSame(SeoIndexability::NotIndexable, $removed->indexability);
        self::assertSame(RobotsPolicy::NoIndexFollow, $removed->robots);
        self::assertSame(ExpiredListingTreatment::Remove, $removed->expiredTreatment);
        self::assertSame(SeoPageTreatment::Remove, $removed->pageTreatment);
        self::assertSame(ExpiredListingTreatment::RetainNoIndex, $retained->expiredTreatment);
        self::assertSame(SeoPageTreatment::Retain, $retained->pageTreatment);
    }

    public function test_non_indexable_page_is_retained_only_by_explicit_source_treatment(): void
    {
        $decision = $this->decision(state: ListingSeoState::NotPublished, nonIndexablePageTreatment: SeoPageTreatment::Retain);

        self::assertSame(SeoIndexability::NotIndexable, $decision->indexability);
        self::assertSame(SeoPageTreatment::Retain, $decision->pageTreatment);
        self::assertSame(RobotsPolicy::NoIndexFollow, $decision->robots);
    }

    public function test_listing_identity_is_never_used_as_a_slug_or_canonical_fallback(): void
    {
        $decision = $this->decision(path: 'annonces/decision-editoriale-explicite');

        self::assertSame('https://appart.sn/annonces/decision-editoriale-explicite', $decision->canonical->value);
        self::assertStringNotContainsString($decision->listingId->value, $decision->canonical->value);
    }

    public function test_missing_canonical_path_is_rejected_instead_of_being_invented(): void
    {
        $this->expectException(InvalidSeoValue::class);

        $this->decision(path: '');
    }

    public function test_same_sources_and_decision_date_reconstruct_the_same_decision(): void
    {
        $first = $this->decision();
        $second = $this->decision();

        self::assertEquals($first, $second);
    }

    /** @param list<CanonicalHistoryEntry> $history */
    private function decision(
        ListingSeoState $state = ListingSeoState::Published,
        string $path = 'annonces/appartement-moderne-dakar',
        string $headline = 'Appartement moderne a Dakar',
        string $description = 'Decouvrez cet appartement moderne, lumineux et bien situe au coeur de Dakar pour votre prochain logement.',
        bool $geography = true,
        bool $media = true,
        array $history = [],
        int $atMinute = 2,
        ExpiredListingTreatment $expiredTreatment = ExpiredListingTreatment::NotApplicable,
        SeoPageTreatment $nonIndexablePageTreatment = SeoPageTreatment::Remove,
    ): ListingSeoDecision {
        [, $search, $property] = $this->sources(1, $state);
        $listing = new ListingSeoSource(
            $this->listingId(),
            $state,
            $headline,
            $description,
            $path,
            $this->revision(SeoSourceKind::Listing, 1),
            $this->at(1),
            $this->at(120),
            $expiredTreatment,
            $nonIndexablePageTreatment,
        );

        return $this->policy->decide(
            $listing,
            $search,
            $property,
            $geography ? $this->geography() : null,
            $media ? new PublicMediaSeoSource($this->listingId(), PublicMediaUrl::fromString('https://media.appart.sn/listings/primary.webp')) : null,
            $history,
            $this->at($atMinute),
        );
    }

    private function geography(): PublicGeographySeoSource
    {
        return new PublicGeographySeoSource($this->listingId(), 'Dakar', [
            new BreadcrumbItem('Accueil', CanonicalUrl::fromString('https://appart.sn/accueil')),
            new BreadcrumbItem('Dakar', CanonicalUrl::fromString('https://appart.sn/dakar')),
        ]);
    }
}

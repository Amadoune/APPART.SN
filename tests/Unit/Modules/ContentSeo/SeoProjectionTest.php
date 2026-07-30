<?php

namespace Tests\Unit\Modules\ContentSeo;

use Appart\Modules\ContentSeo\Domain\Event\CanonicalChanged;
use Appart\Modules\ContentSeo\Domain\Event\SeoProjectionGenerated;
use Appart\Modules\ContentSeo\Domain\Event\SeoProjectionUpdated;
use Appart\Modules\ContentSeo\Domain\Event\SitemapChanged;
use Appart\Modules\ContentSeo\Domain\Exception\DuplicateSeoFact;
use Appart\Modules\ContentSeo\Domain\Exception\InconsistentSeoSources;
use Appart\Modules\ContentSeo\Domain\Exception\SeoViolation;
use Appart\Modules\ContentSeo\Domain\Exception\StaleSeoProjection;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\SearchSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\SeoProjection;
use Appart\Modules\ContentSeo\Domain\Policy\CanonicalHistoryPolicy;
use Appart\Modules\ContentSeo\Domain\Policy\CanonicalPolicy;
use Appart\Modules\ContentSeo\Domain\Policy\SeoFreshnessPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\RobotsPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionId;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceKind;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceRevision;

final class SeoProjectionTest extends ContentSeoTestCase
{
    public function test_generation_creates_complete_projection_and_event(): void
    {
        $projection = $this->projection();
        $material = $projection->document()->material;
        self::assertSame(SeoProjectionState::Active, $material->state);
        self::assertSame(RobotsPolicy::IndexFollow, $material->robots);
        self::assertTrue($material->inSitemap);
        self::assertInstanceOf(SeoProjectionGenerated::class, $projection->releaseEvents()[0]);
    }

    public function test_update_rebuilds_content_from_newer_sources(): void
    {
        $projection = $this->projection();
        $projection->releaseEvents();
        $projection->update($this->material($this->sources(2)), $this->at(2), new SeoFreshnessPolicy);
        self::assertSame(2, $projection->version());
        self::assertInstanceOf(SeoProjectionUpdated::class, $projection->releaseEvents()[0]);
    }

    public function test_non_published_listing_removes_projection_from_index_and_sitemap(): void
    {
        $projection = $this->projection();
        $projection->releaseEvents();
        $projection->update($this->material($this->sources(2, ListingSeoState::NotPublished)), $this->at(2), new SeoFreshnessPolicy);
        $material = $projection->document()->material;
        self::assertSame(SeoProjectionState::Removed, $material->state);
        self::assertSame(RobotsPolicy::NoIndexFollow, $material->robots);
        self::assertFalse($material->inSitemap);
        self::assertInstanceOf(SitemapChanged::class, $projection->releaseEvents()[1]);
    }

    public function test_removed_projection_can_be_reconstructed_after_republication(): void
    {
        $projection = $this->projection();
        $projection->update($this->material($this->sources(2, ListingSeoState::Terminal)), $this->at(2), new SeoFreshnessPolicy);
        $projection->releaseEvents();
        $projection->update($this->material($this->sources(3)), $this->at(3), new SeoFreshnessPolicy);
        self::assertSame(SeoProjectionState::Active, $projection->document()->material->state);
        self::assertTrue($projection->document()->material->inSitemap);
    }

    public function test_canonical_change_updates_structured_data_and_event(): void
    {
        $projection = $this->projection();
        $projection->releaseEvents();
        $projection->changeCanonical((new CanonicalPolicy)->fromPath('annonces/nouvelle-url'), $this->at(2), new CanonicalHistoryPolicy);
        $event = $projection->releaseEvents()[0];
        self::assertInstanceOf(CanonicalChanged::class, $event);
        self::assertSame('https://appart.sn/annonces/nouvelle-url', $projection->document()->material->structuredData->facts['url']);
    }

    public function test_duplicate_source_facts_do_not_mutate_projection(): void
    {
        $projection = $this->projection();
        $projection->releaseEvents();
        $this->expectException(DuplicateSeoFact::class);
        try {
            $projection->update($this->material($this->sources(1)), $this->at(2), new SeoFreshnessPolicy);
        } finally {
            self::assertSame(1, $projection->version());
            self::assertSame([], $projection->releaseEvents());
        }
    }

    public function test_stale_source_facts_do_not_overwrite_projection(): void
    {
        $projection = $this->projection(2);
        $this->expectException(StaleSeoProjection::class);
        $projection->update($this->material($this->sources(1)), $this->at(3), new SeoFreshnessPolicy);
    }

    public function test_release_events_prevents_replay(): void
    {
        $projection = $this->projection();
        self::assertNotEmpty($projection->releaseEvents());
        self::assertSame([], $projection->releaseEvents());
    }

    public function test_canonical_history_is_reserved_for_future_redirect(): void
    {
        $projection = $this->projection();
        $projection->changeCanonical((new CanonicalPolicy)->fromPath('annonces/nouvelle-url'), $this->at(2), new CanonicalHistoryPolicy);
        $history = $projection->canonicalHistory();
        self::assertCount(2, $history);
        self::assertSame('reserved_for_redirect', $history[0]->disposition->value);
        self::assertSame($history[1]->canonical->value, $history[0]->redirectTarget?->value);
    }

    public function test_historical_canonical_cannot_be_reused(): void
    {
        $projection = $this->projection();
        $policy = new CanonicalHistoryPolicy;
        $canonical = new CanonicalPolicy;
        $projection->changeCanonical($canonical->fromPath('annonces/nouvelle-url'), $this->at(2), $policy);
        $this->expectException(SeoViolation::class);
        $projection->changeCanonical($canonical->fromPath('annonces/appartement-moderne-dakar'), $this->at(3), $policy);
    }

    public function test_backdated_canonical_is_rejected_without_mutation_or_event(): void
    {
        $projection = $this->projection(2);
        $projection->releaseEvents();
        $this->expectException(SeoViolation::class);
        try {
            $projection->changeCanonical((new CanonicalPolicy)->fromPath('annonces/nouvelle-url'), $this->at(1), new CanonicalHistoryPolicy);
        } finally {
            self::assertSame(1, $projection->version());
            self::assertSame([], $projection->releaseEvents());
        }
    }

    public function test_cross_source_incoherence_is_rejected_without_mutation(): void
    {
        $projection = $this->projection();
        $projection->releaseEvents();
        [$listing, $search, $property] = $this->sources(2);
        $revision = SeoSourceRevision::create(SeoSourceKind::Search, 2, '82000000-0000-4000-8000-000000000001', '83000000-0000-4000-8000-000000000001', $this->at(2));
        $search = new SearchSeoSource($search->listingId, $search->state, $revision);
        $this->expectException(InconsistentSeoSources::class);
        try {
            $projection->update($this->material([$listing, $search, $property]), $this->at(2), new SeoFreshnessPolicy);
        } finally {
            self::assertSame(1, $projection->version());
            self::assertSame([], $projection->releaseEvents());
        }
    }

    public function test_source_derived_canonical_change_updates_history(): void
    {
        $projection = $this->projection();
        [$listing, $search, $property] = $this->sources(2);
        $listing = new ListingSeoSource($listing->listingId, $listing->state, $listing->headline, $listing->description, 'annonces/url-issue-source', $listing->revision);
        $projection->update($this->material([$listing, $search, $property]), $this->at(2), new SeoFreshnessPolicy);
        self::assertCount(2, $projection->canonicalHistory());
        self::assertSame('https://appart.sn/annonces/url-issue-source', $projection->canonicalHistory()[0]->redirectTarget?->value);
    }

    public function test_every_historical_url_points_directly_to_latest_canonical(): void
    {
        $projection = $this->projection();
        $canonical = new CanonicalPolicy;
        $history = new CanonicalHistoryPolicy;
        $projection->changeCanonical($canonical->fromPath('annonces/deuxieme'), $this->at(2), $history);
        $projection->changeCanonical($canonical->fromPath('annonces/troisieme'), $this->at(3), $history);
        self::assertSame('https://appart.sn/annonces/troisieme', $projection->canonicalHistory()[0]->redirectTarget?->value);
        self::assertSame('https://appart.sn/annonces/troisieme', $projection->canonicalHistory()[1]->redirectTarget?->value);
    }

    private function projection(int $version = 1): SeoProjection
    {
        return SeoProjection::generate(SeoProjectionId::fromString('a0000000-0000-4000-8000-000000000001'), $this->listingId(), $this->material($this->sources($version)), $this->at($version));
    }
}

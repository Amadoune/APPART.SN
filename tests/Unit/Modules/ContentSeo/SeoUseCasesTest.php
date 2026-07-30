<?php

namespace Tests\Unit\Modules\ContentSeo;

use Appart\Modules\ContentSeo\Application\UseCase\ChangeCanonical;
use Appart\Modules\ContentSeo\Application\UseCase\GenerateSeoProjection;
use Appart\Modules\ContentSeo\Application\UseCase\RemoveSeoProjection;
use Appart\Modules\ContentSeo\Application\UseCase\SeoSources;
use Appart\Modules\ContentSeo\Application\UseCase\UpdateSeoProjection;
use Appart\Modules\ContentSeo\Domain\Exception\CanonicalUrlConflict;
use Appart\Modules\ContentSeo\Domain\Exception\ConcurrentSeoModification;
use Appart\Modules\ContentSeo\Domain\Exception\ListingSeoConflict;
use Appart\Modules\ContentSeo\Domain\Exception\SeoSourceUnavailable;
use Appart\Modules\ContentSeo\Domain\Exception\SeoViolation;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\SeoProjection;
use Appart\Modules\ContentSeo\Domain\Policy\CanonicalHistoryPolicy;
use Appart\Modules\ContentSeo\Domain\Policy\CanonicalPolicy;
use Appart\Modules\ContentSeo\Domain\Policy\SeoFreshnessPolicy;
use Appart\Modules\ContentSeo\Domain\Policy\SeoGenerationPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\RobotsPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionId;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionState;
use Tests\Unit\Modules\ContentSeo\Support\FakeListingCatalog;
use Tests\Unit\Modules\ContentSeo\Support\FakePropertyCatalog;
use Tests\Unit\Modules\ContentSeo\Support\FakeSearchCatalog;
use Tests\Unit\Modules\ContentSeo\Support\FakeSeoProjectionRegistry;

final class SeoUseCasesTest extends ContentSeoTestCase
{
    private FakeSeoProjectionRegistry $registry;

    private FakeListingCatalog $listings;

    private FakeSearchCatalog $search;

    private FakePropertyCatalog $properties;

    private GenerateSeoProjection $generate;

    private UpdateSeoProjection $update;

    private RemoveSeoProjection $remove;

    private ChangeCanonical $canonical;

    private SeoProjectionId $id;

    protected function setUp(): void
    {
        $this->registry = new FakeSeoProjectionRegistry;
        $this->listings = new FakeListingCatalog;
        $this->search = new FakeSearchCatalog;
        $this->properties = new FakePropertyCatalog;
        $sources = new SeoSources($this->listings, $this->search, $this->properties);
        $policy = new SeoGenerationPolicy(new CanonicalPolicy);
        $this->generate = new GenerateSeoProjection($this->registry, $sources, $policy);
        $this->update = new UpdateSeoProjection($this->registry, $sources, $policy, new SeoFreshnessPolicy);
        $this->remove = new RemoveSeoProjection($this->registry, $sources, $policy, new SeoFreshnessPolicy);
        $this->canonical = new ChangeCanonical($this->registry, new CanonicalPolicy, new CanonicalHistoryPolicy);
        $this->id = SeoProjectionId::fromString('a0000000-0000-4000-8000-000000000001');
        $this->provide(1);
    }

    public function test_generate_stores_detached_event_free_snapshot(): void
    {
        $returned = $this->create();
        self::assertNotEmpty($returned->releaseEvents());
        self::assertSame([], $this->registry->find($this->id)?->releaseEvents());
    }

    public function test_generation_is_refused_when_listing_is_not_publishable(): void
    {
        $this->provide(1, ListingSeoState::NotPublished);
        $this->expectException(SeoViolation::class);
        $this->create();
    }

    public function test_missing_source_is_explicit(): void
    {
        $this->expectException(SeoSourceUnavailable::class);
        $this->generate->execute($this->id, $this->listingId(99), $this->at(1));
    }

    public function test_update_and_remove_are_derived_from_sources(): void
    {
        $this->create();
        $this->provide(2, ListingSeoState::NotPublished);
        $this->remove->execute($this->id, $this->at(2));
        self::assertSame(SeoProjectionState::Removed, $this->registry->find($this->id)?->document()->material->state);
    }

    public function test_remove_is_refused_while_sources_are_publishable(): void
    {
        $this->create();
        $this->provide(2);
        $this->expectException(SeoViolation::class);
        $this->remove->execute($this->id, $this->at(2));
    }

    public function test_canonical_change_is_atomic_and_unique(): void
    {
        $this->create();
        $this->canonical->execute($this->id, 'annonces/nouvelle-url', $this->at(2));
        self::assertSame('https://appart.sn/annonces/nouvelle-url', $this->registry->find($this->id)?->document()->material->canonical->value);
    }

    public function test_listing_has_only_one_projection(): void
    {
        $this->create();
        $this->expectException(ListingSeoConflict::class);
        $this->generate->execute(SeoProjectionId::fromString('a0000000-0000-4000-8000-000000000002'), $this->listingId(), $this->at(2));
    }

    public function test_canonical_is_globally_unique(): void
    {
        $this->create();
        $this->provide(1, id: $this->listingId(2));
        $this->expectException(CanonicalUrlConflict::class);
        $this->generate->execute(SeoProjectionId::fromString('a0000000-0000-4000-8000-000000000002'), $this->listingId(2), $this->at(1));
    }

    public function test_failed_save_does_not_leak_mutation(): void
    {
        $this->create();
        $this->provide(2, ListingSeoState::NotPublished);
        $this->registry->failNextWrite();
        $this->expectException(ConcurrentSeoModification::class);
        try {
            $this->update->execute($this->id, $this->at(2));
        } finally {
            self::assertSame(SeoProjectionState::Active, $this->registry->find($this->id)?->document()->material->state);
        }
    }

    public function test_missing_source_applies_fail_safe_to_existing_projection(): void
    {
        $this->create();
        $this->listings->remove($this->listingId());
        $this->update->execute($this->id, $this->at(2));
        $material = $this->registry->find($this->id)?->document()->material;
        self::assertSame(SeoProjectionState::Removed, $material?->state);
        self::assertSame(RobotsPolicy::NoIndexFollow, $material?->robots);
        self::assertFalse($material?->inSitemap);
    }

    public function test_invalid_content_cannot_prevent_fail_safe_removal(): void
    {
        $this->create();
        [$listing] = $this->sources(2, ListingSeoState::NotPublished);
        $this->listings->set(new ListingSeoSource($listing->listingId, $listing->state, '', '', '', $listing->revision));
        [, $search, $property] = $this->sources(2, ListingSeoState::NotPublished);
        $this->search->set($search);
        $this->properties->set($property);
        $this->update->execute($this->id, $this->at(2));
        self::assertSame(SeoProjectionState::Removed, $this->registry->find($this->id)?->document()->material->state);
    }

    public function test_canonical_rollback_preserves_snapshot_and_history(): void
    {
        $this->create();
        $this->registry->failNextWrite();
        $this->expectException(ConcurrentSeoModification::class);
        try {
            $this->canonical->execute($this->id, 'annonces/nouvelle-url', $this->at(2));
        } finally {
            $stored = $this->registry->find($this->id);
            self::assertSame('https://appart.sn/annonces/appartement-moderne-dakar', $stored?->document()->material->canonical->value);
            self::assertCount(1, $stored?->canonicalHistory() ?? []);
        }
    }

    public function test_concurrent_canonical_change_rejects_stale_snapshot(): void
    {
        $this->create();
        $first = $this->registry->find($this->id);
        $stale = $this->registry->find($this->id);
        self::assertNotNull($first);
        self::assertNotNull($stale);
        $policy = new CanonicalHistoryPolicy;
        $first->changeCanonical((new CanonicalPolicy)->fromPath('annonces/premiere-url'), $this->at(2), $policy);
        $this->registry->save($first, 1);
        $stale->changeCanonical((new CanonicalPolicy)->fromPath('annonces/seconde-url'), $this->at(2), $policy);
        $this->expectException(ConcurrentSeoModification::class);
        $this->registry->save($stale, 1);
    }

    public function test_historical_canonical_remains_globally_reserved(): void
    {
        $this->create();
        $this->canonical->execute($this->id, 'annonces/nouvelle-url', $this->at(2));
        $this->provide(1, id: $this->listingId(2));
        $this->expectException(CanonicalUrlConflict::class);
        $this->generate->execute(SeoProjectionId::fromString('a0000000-0000-4000-8000-000000000002'), $this->listingId(2), $this->at(1));
    }

    private function create(): SeoProjection
    {
        return $this->generate->execute($this->id, $this->listingId(), $this->at(1));
    }

    private function provide(int $version, ListingSeoState $state = ListingSeoState::Published, ?ListingId $id = null): void
    {
        [$listing, $search, $property] = $this->sources($version, $state, id: $id);
        $this->listings->set($listing);
        $this->search->set($search);
        $this->properties->set($property);
    }
}

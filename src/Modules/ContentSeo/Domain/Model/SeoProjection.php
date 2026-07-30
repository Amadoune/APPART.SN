<?php

namespace Appart\Modules\ContentSeo\Domain\Model;

use Appart\Modules\ContentSeo\Domain\Event\AbstractSeoEvent;
use Appart\Modules\ContentSeo\Domain\Event\CanonicalChanged;
use Appart\Modules\ContentSeo\Domain\Event\SeoEvent;
use Appart\Modules\ContentSeo\Domain\Event\SeoFailSafeApplied;
use Appart\Modules\ContentSeo\Domain\Event\SeoProjectionGenerated;
use Appart\Modules\ContentSeo\Domain\Event\SeoProjectionUpdated;
use Appart\Modules\ContentSeo\Domain\Event\SitemapChanged;
use Appart\Modules\ContentSeo\Domain\Exception\SeoViolation;
use Appart\Modules\ContentSeo\Domain\Policy\CanonicalHistoryPolicy;
use Appart\Modules\ContentSeo\Domain\Policy\SeoFreshnessPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalDisposition;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoFailSafeReason;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionId;
use DateTimeImmutable;

final class SeoProjection
{
    /** @var list<SeoEvent> */
    private array $events = [];

    private int $version = 0;

    /** @param list<CanonicalHistoryEntry> $canonicalHistory */
    private function __construct(private readonly SeoProjectionId $id, private readonly ListingId $listingId, private SeoDocument $document, private array $canonicalHistory) {}

    public static function generate(SeoProjectionId $id, ListingId $listingId, SeoMaterial $material, DateTimeImmutable $at): self
    {
        $self = new self($id, $listingId, SeoDocument::generate($listingId, $material, $at), [new CanonicalHistoryEntry($material->canonical, CanonicalDisposition::Current, $at)]);
        $self->version = 1;
        $self->addEvent(new SeoProjectionGenerated($id, $listingId, $material, $at));

        return $self;
    }

    /** @param list<CanonicalHistoryEntry> $canonicalHistory */
    public static function reconstitute(SeoProjectionId $id, ListingId $listingId, SeoDocument $document, array $canonicalHistory, int $version): self
    {
        if ($version < 1 || $canonicalHistory === []) {
            throw new SeoViolation('Persisted SEO projection is invalid.');
        }
        $projection = new self($id, $listingId, $document, $canonicalHistory);
        $projection->version = $version;

        return $projection;
    }

    public function update(SeoMaterial $material, DateTimeImmutable $at, SeoFreshnessPolicy $freshness): void
    {
        $this->guardTime($at);
        $freshness->assertNewer($material->revisions, $this->document->material->revisions);
        $previous = $this->document->material;
        if ($previous->canonical->value !== $material->canonical->value) {
            $this->canonicalHistory = (new CanonicalHistoryPolicy)->replace($this->canonicalHistory, $previous->canonical, $material->canonical, $at);
        }
        $this->document = $this->document->update($material, $at);
        $this->version++;
        $this->addEvent(new SeoProjectionUpdated($this->id, $this->listingId, $material, $at));
        if ($previous->canonical->value !== $material->canonical->value) {
            $this->addEvent(new CanonicalChanged($this->id, $this->listingId, $previous->canonical, $material->canonical, $at));
        }
        if ($previous->inSitemap !== $material->inSitemap || $previous->canonical->value !== $material->canonical->value) {
            $this->addEvent(new SitemapChanged($this->id, $this->listingId, $previous->inSitemap, $material->inSitemap, $previous->canonical, $material->canonical, $at));
        }
    }

    public function changeCanonical(CanonicalUrl $canonical, DateTimeImmutable $at, CanonicalHistoryPolicy $historyPolicy): void
    {
        $this->guardTime($at);
        $previous = $this->document->material;
        $material = SeoMaterial::derived($previous->state, $previous->title, $previous->description, $canonical, $previous->robots, new StructuredData($previous->structuredData->type, [...$previous->structuredData->facts, 'url' => $canonical->value]), $previous->inSitemap, $previous->sitemapPriority, $previous->revisions);
        $history = $historyPolicy->replace($this->canonicalHistory, $previous->canonical, $canonical, $at);
        $this->document = $this->document->update($material, $at);
        $this->canonicalHistory = $history;
        $this->version++;
        $this->addEvent(new CanonicalChanged($this->id, $this->listingId, $previous->canonical, $canonical, $at));
        if ($previous->inSitemap) {
            $this->addEvent(new SitemapChanged($this->id, $this->listingId, true, true, $previous->canonical, $canonical, $at));
        }
    }

    public function id(): SeoProjectionId
    {
        return $this->id;
    }

    public function listingId(): ListingId
    {
        return $this->listingId;
    }

    public function document(): SeoDocument
    {
        return $this->document;
    }

    public function version(): int
    {
        return $this->version;
    }

    public function failSafe(SeoFailSafeReason $reason, DateTimeImmutable $at): void
    {
        $this->guardTime($at);
        $previous = $this->document->material;
        $material = $previous->failSafe();
        if (! $previous->inSitemap && $previous->robots === $material->robots) {
            throw new SeoViolation('Projection is already fail-safe.');
        }
        $this->document = $this->document->update($material, $at);
        $this->version++;
        $this->addEvent(new SeoFailSafeApplied($this->id, $this->listingId, $reason, $at));
        $this->addEvent(new SeoProjectionUpdated($this->id, $this->listingId, $material, $at));
        if ($previous->inSitemap) {
            $this->addEvent(new SitemapChanged($this->id, $this->listingId, true, false, $previous->canonical, $previous->canonical, $at));
        }
    }

    /** @return list<CanonicalHistoryEntry> */
    public function canonicalHistory(): array
    {
        return $this->canonicalHistory;
    }

    /** @return list<SeoEvent> */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    private function guardTime(DateTimeImmutable $at): void
    {
        if ($at < $this->document->updatedAt) {
            throw new SeoViolation('SEO mutation cannot predate the current projection.');
        }
    }

    private function addEvent(SeoEvent $event): void
    {
        if ($event instanceof AbstractSeoEvent) {
            $event->stamp($this->version, count($this->events) + 1);
        }
        $this->events[] = $event;
    }
}

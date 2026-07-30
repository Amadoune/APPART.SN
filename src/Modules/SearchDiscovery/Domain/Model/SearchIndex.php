<?php

namespace Appart\Modules\SearchDiscovery\Domain\Model;

use Appart\Modules\SearchDiscovery\Domain\Event\AbstractSearchIndexEvent;
use Appart\Modules\SearchDiscovery\Domain\Event\SearchDocumentIndexed;
use Appart\Modules\SearchDiscovery\Domain\Event\SearchDocumentRebuilt;
use Appart\Modules\SearchDiscovery\Domain\Event\SearchDocumentRemoved;
use Appart\Modules\SearchDiscovery\Domain\Event\SearchDocumentUpdated;
use Appart\Modules\SearchDiscovery\Domain\Event\SearchIndexEvent;
use Appart\Modules\SearchDiscovery\Domain\Event\SearchVisibilityChanged;
use Appart\Modules\SearchDiscovery\Domain\Exception\DuplicateProjectionFact;
use Appart\Modules\SearchDiscovery\Domain\Exception\InconsistentProjectionSources;
use Appart\Modules\SearchDiscovery\Domain\Exception\StaleProjection;
use Appart\Modules\SearchDiscovery\Domain\Policy\ProjectionLifecyclePolicy;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchFreshnessPolicy;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\FreshnessDecision;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ProjectionChange;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ProjectionState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchIndexId;
use DateTimeImmutable;

final class SearchIndex
{
    /** @var list<SearchIndexEvent> */
    private array $events = [];

    private int $version = 0;

    private function __construct(
        private readonly SearchIndexId $id,
        private readonly ListingId $listingId,
        private SearchDocument $document,
    ) {}

    public static function create(SearchIndexId $id, SearchDocumentId $documentId, ListingId $listingId, SearchProjection $projection, DateTimeImmutable $at): self
    {
        $self = new self($id, $listingId, SearchDocument::index($documentId, $listingId, $projection, $at));
        $self->version = 1;
        $self->addEvent(new SearchDocumentIndexed($id, $documentId, $listingId, $projection, $at));

        return $self;
    }

    public static function reconstitute(SearchIndexId $id, ListingId $listingId, SearchDocument $document, int $version): self
    {
        if ($version < 1 || ! $document->listingId->equals($listingId)) {
            throw new InconsistentProjectionSources;
        }
        $index = new self($id, $listingId, $document);
        $index->version = $version;

        return $index;
    }

    public function synchronize(SearchProjection $projection, DateTimeImmutable $at, SearchFreshnessPolicy $freshness, ProjectionLifecyclePolicy $lifecycle): void
    {
        $decision = $freshness->compare($projection->revisions, $this->document->projection->revisions);
        if ($decision === FreshnessDecision::Duplicate) {
            throw new DuplicateProjectionFact;
        }
        if ($decision === FreshnessDecision::Stale) {
            throw new StaleProjection;
        }
        if ($decision === FreshnessDecision::Inconsistent) {
            throw new InconsistentProjectionSources;
        }

        $previous = $this->document->state();
        $change = $lifecycle->decide($previous, $projection->state);
        $this->document = $this->document->project($projection, $at);
        $this->version++;
        $this->recordEvents($previous, $projection, $change, $at);
    }

    public function id(): SearchIndexId
    {
        return $this->id;
    }

    public function listingId(): ListingId
    {
        return $this->listingId;
    }

    public function document(): SearchDocument
    {
        return $this->document;
    }

    public function version(): int
    {
        return $this->version;
    }

    /** @return list<SearchIndexEvent> */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    private function recordEvents(ProjectionState $previous, SearchProjection $projection, ProjectionChange $change, DateTimeImmutable $at): void
    {
        if ($previous !== $projection->state) {
            $this->addEvent(new SearchVisibilityChanged($this->id, $this->document->id, $this->listingId, $previous, $projection->state, $at));
        }
        $this->addEvent(match ($change) {
            ProjectionChange::Removed => new SearchDocumentRemoved($this->id, $this->document->id, $this->listingId, $projection, $at),
            ProjectionChange::Rebuilt => new SearchDocumentRebuilt($this->id, $this->document->id, $this->listingId, $projection, $at),
            default => new SearchDocumentUpdated($this->id, $this->document->id, $this->listingId, $projection, $change, $at),
        });
    }

    private function addEvent(SearchIndexEvent $event): void
    {
        if ($event instanceof AbstractSearchIndexEvent) {
            $event->stamp($this->version, count($this->events) + 1);
        }
        $this->events[] = $event;
    }
}

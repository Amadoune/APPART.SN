<?php

namespace Appart\Modules\SearchDiscovery\Application\UseCase;

use Appart\Modules\SearchDiscovery\Application\Contract\SearchIndexRegistry;
use Appart\Modules\SearchDiscovery\Domain\Policy\ProjectionLifecyclePolicy;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchFreshnessPolicy;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchProjectionPolicy;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchIndexId;
use DateTimeImmutable;

final readonly class UpdateIndex extends SearchIndexUseCase
{
    public function __construct(
        SearchIndexRegistry $indexes,
        private ProjectionSources $sources,
        private SearchProjectionPolicy $projections,
        private SearchFreshnessPolicy $freshness,
        private ProjectionLifecyclePolicy $lifecycle,
    ) {
        parent::__construct($indexes);
    }

    public function execute(SearchIndexId $indexId, DateTimeImmutable $at): void
    {
        [$index, $version] = $this->load($indexId);
        [$listing, $property, $media] = $this->sources->load($index->listingId());
        $index->synchronize($this->projections->build($listing, $property, $media), $at, $this->freshness, $this->lifecycle);
        $this->indexes->save($index, $version);
    }
}

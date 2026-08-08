<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOutbox;

final readonly class SearchQueryResolutionOutboxResult
{
    public function __construct(
        public string $outboxId,
        public SearchQueryResolutionOutboxStatus $status,
        public string $observedAt,
        public bool $alreadyApplied,
    ) {}
}

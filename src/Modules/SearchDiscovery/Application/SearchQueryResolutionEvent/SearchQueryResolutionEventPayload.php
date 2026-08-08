<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionEvent;

final readonly class SearchQueryResolutionEventPayload
{
    public function __construct(
        public SearchQueryResolutionEventStatus $status,
        public string $observedAt,
    ) {}

    /** @return array{status: string, observedAt: string} */
    public function canonical(): array
    {
        return [
            'status' => $this->status->value,
            'observedAt' => $this->observedAt,
        ];
    }
}

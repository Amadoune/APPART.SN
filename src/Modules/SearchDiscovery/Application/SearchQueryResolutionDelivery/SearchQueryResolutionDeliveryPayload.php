<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionDelivery;

final readonly class SearchQueryResolutionDeliveryPayload
{
    public function __construct(
        public SearchQueryResolutionDeliveryStatus $status,
        public string $observedAt,
    ) {}

    /** @return array{status: string, observedAt: string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}

<?php

namespace Appart\Modules\SearchDiscovery\Application\Materialization;

use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecision;

final readonly class PublicSearchMaterializationResult
{
    private function __construct(
        public PublicSearchMaterializationStatus $status,
        public ?SearchDecision $decision = null,
    ) {}

    public static function of(PublicSearchMaterializationStatus $status, ?SearchDecision $decision = null): self
    {
        return new self($status, $decision);
    }
}

<?php

namespace App\Application\PublicProjectionReconciliation;

final readonly class PublicProjectionReconciliationResult
{
    /** @param list<PublicProjectionReconciliationAnalysis> $analyses */
    public function __construct(public int $observed, public int $scheduled, public array $analyses, public ?string $nextCheckpoint) {}
}

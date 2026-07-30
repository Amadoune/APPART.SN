<?php

namespace App\Application\PublicProjectionReconciliation;

final readonly class PublicProjectionReconciliationPage
{
    /** @param list<PublicProjectionReconciliationObservation> $observations */
    public function __construct(public array $observations, public ?string $nextCheckpoint) {}
}

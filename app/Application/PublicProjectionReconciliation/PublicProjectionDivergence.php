<?php

namespace App\Application\PublicProjectionReconciliation;

final readonly class PublicProjectionDivergence
{
    public function __construct(public PublicProjectionDivergenceType $type, public PublicProjectionReconciliationObservation $observation) {}
}

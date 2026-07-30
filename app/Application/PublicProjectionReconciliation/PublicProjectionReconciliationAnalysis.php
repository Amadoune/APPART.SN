<?php

namespace App\Application\PublicProjectionReconciliation;

final readonly class PublicProjectionReconciliationAnalysis
{
    public function __construct(public PublicProjectionDivergence $divergence, public PublicProjectionReconciliationDecision $decision, public string $explanation) {}
}

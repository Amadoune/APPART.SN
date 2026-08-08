<?php

namespace Appart\Modules\ReliabilityOperations\Application\PublicRead;

final readonly class CapacityPlanningResultV1
{
    public string $observedAt;

    public function __construct(public CapacityPlanningStatusV1 $status, ReliabilityOperationsObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}

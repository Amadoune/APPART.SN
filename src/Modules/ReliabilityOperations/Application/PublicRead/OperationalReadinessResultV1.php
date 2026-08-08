<?php

namespace Appart\Modules\ReliabilityOperations\Application\PublicRead;

final readonly class OperationalReadinessResultV1
{
    public string $observedAt;

    public function __construct(public OperationalReadinessStatusV1 $status, ReliabilityOperationsObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}

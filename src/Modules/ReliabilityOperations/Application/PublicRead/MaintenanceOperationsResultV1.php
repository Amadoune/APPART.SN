<?php

namespace Appart\Modules\ReliabilityOperations\Application\PublicRead;

final readonly class MaintenanceOperationsResultV1
{
    public string $observedAt;

    public function __construct(public MaintenanceOperationsStatusV1 $status, ReliabilityOperationsObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}

<?php

namespace Appart\Modules\ReliabilityOperations\Application\PublicRead;

final readonly class ServiceHealthResultV1
{
    public string $observedAt;

    public function __construct(public ServiceHealthStatusV1 $status, ReliabilityOperationsObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}

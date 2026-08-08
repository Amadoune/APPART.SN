<?php

namespace Appart\Modules\ReliabilityOperations\Application\PublicRead;

final readonly class AlertingResultV1
{
    public string $observedAt;

    public function __construct(public AlertingStatusV1 $status, ReliabilityOperationsObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}

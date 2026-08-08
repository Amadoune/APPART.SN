<?php

namespace Appart\Modules\ReliabilityOperations\Application\PublicRead;

final readonly class ObservabilityResultV1
{
    public string $observedAt;

    public function __construct(public ObservabilityStatusV1 $status, ReliabilityOperationsObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}

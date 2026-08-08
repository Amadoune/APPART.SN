<?php

namespace Appart\Modules\ReliabilityOperations\Application\PublicRead;

final readonly class ContinuityResultV1
{
    public string $observedAt;

    public function __construct(public ContinuityStatusV1 $status, ReliabilityOperationsObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}

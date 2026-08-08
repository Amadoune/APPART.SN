<?php

namespace Appart\Modules\SecurityCompliance\Application\PublicRead;

final readonly class DataRetentionResultV1
{
    public string $observedAt;

    public function __construct(public DataRetentionStatusV1 $status, SecurityComplianceObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}

<?php

namespace Appart\Modules\SecurityCompliance\Application\PublicRead;

final readonly class DataExportResultV1
{
    public string $observedAt;

    public function __construct(public DataExportStatusV1 $status, SecurityComplianceObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}

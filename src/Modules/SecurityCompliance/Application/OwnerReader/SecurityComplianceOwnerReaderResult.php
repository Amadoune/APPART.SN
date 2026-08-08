<?php

namespace Appart\Modules\SecurityCompliance\Application\OwnerReader;

final readonly class SecurityComplianceOwnerReaderResult
{
    public function __construct(public SecurityComplianceOwnerReaderStatus $status) {}
}

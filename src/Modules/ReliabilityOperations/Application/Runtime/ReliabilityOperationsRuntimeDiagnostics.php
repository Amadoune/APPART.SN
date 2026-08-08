<?php

namespace Appart\Modules\ReliabilityOperations\Application\Runtime;

final readonly class ReliabilityOperationsRuntimeDiagnostics
{
    public function __construct(
        public string $runtimeId,
        public string $version,
        public ReliabilityOperationsRuntimeAvailability $availability,
    ) {}
}

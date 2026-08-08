<?php

namespace Appart\Modules\ReliabilityOperations\Application\Runtime;

interface ReliabilityOperationsRuntimeV1
{
    public function availability(): ReliabilityOperationsRuntimeAvailability;

    public function diagnostics(): ReliabilityOperationsRuntimeDiagnostics;
}

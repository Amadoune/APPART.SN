<?php

namespace Appart\Modules\ReliabilityOperations\Application\Runtime;

final readonly class DeterministicReliabilityOperationsRuntime implements ReliabilityOperationsRuntimeV1
{
    private const RUNTIME_ID = 'reliability-operations.owner-source';

    private const VERSION = 'reliability-operations-runtime-v1';

    public function __construct(private ReliabilityOperationsRuntimeAvailabilityPolicy $availabilityPolicy) {}

    public function availability(): ReliabilityOperationsRuntimeAvailability
    {
        return $this->availabilityPolicy->inspect();
    }

    public function diagnostics(): ReliabilityOperationsRuntimeDiagnostics
    {
        return new ReliabilityOperationsRuntimeDiagnostics(self::RUNTIME_ID, self::VERSION, $this->availability());
    }
}

<?php

namespace Appart\Modules\LegacyMigration\Application\Runtime;

final readonly class DeterministicLegacyMigrationRuntime implements LegacyMigrationRuntimeV1
{
    private const RUNTIME_ID = 'legacy-migration.owner-source';

    private const VERSION = 'legacy-migration-runtime-v1';

    public function __construct(private LegacyMigrationRuntimeAvailabilityPolicy $availabilityPolicy) {}

    public function availability(): LegacyMigrationRuntimeAvailability
    {
        return $this->availabilityPolicy->inspect();
    }

    public function diagnostics(): LegacyMigrationRuntimeDiagnostics
    {
        return new LegacyMigrationRuntimeDiagnostics(self::RUNTIME_ID, self::VERSION, $this->availability());
    }
}

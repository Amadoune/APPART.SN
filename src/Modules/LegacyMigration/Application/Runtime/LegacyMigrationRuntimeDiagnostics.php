<?php

namespace Appart\Modules\LegacyMigration\Application\Runtime;

final readonly class LegacyMigrationRuntimeDiagnostics
{
    public function __construct(
        public string $runtimeId,
        public string $version,
        public LegacyMigrationRuntimeAvailability $availability,
    ) {}
}

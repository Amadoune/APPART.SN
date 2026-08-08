<?php

namespace Appart\Modules\LegacyMigration\Application\Runtime;

interface LegacyMigrationRuntimeV1
{
    public function availability(): LegacyMigrationRuntimeAvailability;

    public function diagnostics(): LegacyMigrationRuntimeDiagnostics;
}

<?php

namespace Appart\Modules\LegacyMigration\Application\Runtime;

interface LegacyMigrationRuntimeAvailabilityPolicy
{
    public function inspect(): LegacyMigrationRuntimeAvailability;
}

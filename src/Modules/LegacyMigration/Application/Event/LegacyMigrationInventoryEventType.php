<?php

namespace Appart\Modules\LegacyMigration\Application\Event;

enum LegacyMigrationInventoryEventType: string
{
    case Observed = 'legacy-migration.inventory.observed.v1';
}

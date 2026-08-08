<?php

namespace Appart\Modules\LegacyMigration\Application\Event;

enum LegacyMigrationCutoverEventType: string
{
    case Observed = 'legacy-migration.cutover.observed.v1';
}

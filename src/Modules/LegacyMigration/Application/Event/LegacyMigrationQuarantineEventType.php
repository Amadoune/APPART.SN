<?php

namespace Appart\Modules\LegacyMigration\Application\Event;

enum LegacyMigrationQuarantineEventType: string
{
    case Observed = 'legacy-migration.quarantine.observed.v1';
}

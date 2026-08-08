<?php

namespace Appart\Modules\LegacyMigration\Application\Event;

enum LegacyMigrationReconciliationEventType: string
{
    case Observed = 'legacy-migration.reconciliation.observed.v1';
}

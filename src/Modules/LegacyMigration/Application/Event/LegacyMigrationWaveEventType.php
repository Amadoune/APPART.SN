<?php

namespace Appart\Modules\LegacyMigration\Application\Event;

enum LegacyMigrationWaveEventType: string
{
    case Observed = 'legacy-migration.wave.observed.v1';
}

<?php

namespace Appart\Modules\LegacyMigration\Application\PublicRead\Contract;

use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;

interface LegacyMigrationInventoryReaderV1
{
    public function read(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationInventoryResultV1;
}

<?php

namespace Appart\Modules\LegacyMigration\Application\PublicRead\Contract;

use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;

interface LegacyMigrationCutoverReaderV1
{
    public function read(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationCutoverResultV1;
}

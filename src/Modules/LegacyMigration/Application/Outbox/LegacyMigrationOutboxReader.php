<?php

namespace Appart\Modules\LegacyMigration\Application\Outbox;

interface LegacyMigrationOutboxReader
{
    /** @return list<LegacyMigrationOutboxResult> */
    public function pending(int $limit): array;
}

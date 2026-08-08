<?php

namespace Appart\Modules\LegacyMigration\Application\OwnerReader;

final readonly class LegacyMigrationOwnerReaderResult
{
    public function __construct(public LegacyMigrationOwnerReaderStatus $status) {}
}

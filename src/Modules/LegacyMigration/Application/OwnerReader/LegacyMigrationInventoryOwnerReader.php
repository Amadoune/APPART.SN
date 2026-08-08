<?php

namespace Appart\Modules\LegacyMigration\Application\OwnerReader;

use Appart\Modules\LegacyMigration\Application\OwnerReader\Contract\LegacyMigrationOwnerReaderV1;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationOwnerSource;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationInventoryReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;

final readonly class LegacyMigrationInventoryOwnerReader implements LegacyMigrationInventoryReaderV1
{
    public function __construct(private LegacyMigrationOwnerSource $source, private LegacyMigrationOwnerReaderV1 $policy) {}

    public function read(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationInventoryResultV1
    {
        $status = LegacyMigrationInventoryStatusV1::from($this->policy->inventory($this->source->readInventory($subject, $observedAt))->status->value);

        return new LegacyMigrationInventoryResultV1($status, $observedAt);
    }
}

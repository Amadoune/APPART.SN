<?php

namespace Appart\Modules\LegacyMigration\Application\OwnerReader;

use Appart\Modules\LegacyMigration\Application\OwnerReader\Contract\LegacyMigrationOwnerReaderV1;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationOwnerSource;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationReconciliationReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;

final readonly class LegacyMigrationReconciliationOwnerReader implements LegacyMigrationReconciliationReaderV1
{
    public function __construct(private LegacyMigrationOwnerSource $source, private LegacyMigrationOwnerReaderV1 $policy) {}

    public function read(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationReconciliationResultV1
    {
        $status = LegacyMigrationReconciliationStatusV1::from($this->policy->reconciliation($this->source->readReconciliation($subject, $observedAt))->status->value);

        return new LegacyMigrationReconciliationResultV1($status, $observedAt);
    }
}

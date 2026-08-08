<?php

namespace Appart\Modules\LegacyMigration\Application\OwnerReader;

use Appart\Modules\LegacyMigration\Application\OwnerReader\Contract\LegacyMigrationOwnerReaderV1;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationOwnerSource;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationCutoverReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;

final readonly class LegacyMigrationCutoverOwnerReader implements LegacyMigrationCutoverReaderV1
{
    public function __construct(private LegacyMigrationOwnerSource $source, private LegacyMigrationOwnerReaderV1 $policy) {}

    public function read(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationCutoverResultV1
    {
        $status = LegacyMigrationCutoverStatusV1::from($this->policy->cutover($this->source->readCutover($subject, $observedAt))->status->value);

        return new LegacyMigrationCutoverResultV1($status, $observedAt);
    }
}

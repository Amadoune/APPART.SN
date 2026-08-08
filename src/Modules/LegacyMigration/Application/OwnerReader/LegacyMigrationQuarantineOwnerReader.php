<?php

namespace Appart\Modules\LegacyMigration\Application\OwnerReader;

use Appart\Modules\LegacyMigration\Application\OwnerReader\Contract\LegacyMigrationOwnerReaderV1;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationOwnerSource;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationQuarantineReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationQuarantineResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationQuarantineStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;

final readonly class LegacyMigrationQuarantineOwnerReader implements LegacyMigrationQuarantineReaderV1
{
    public function __construct(private LegacyMigrationOwnerSource $source, private LegacyMigrationOwnerReaderV1 $policy) {}

    public function read(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationQuarantineResultV1
    {
        $status = LegacyMigrationQuarantineStatusV1::from($this->policy->quarantine($this->source->readQuarantine($subject, $observedAt))->status->value);

        return new LegacyMigrationQuarantineResultV1($status, $observedAt);
    }
}

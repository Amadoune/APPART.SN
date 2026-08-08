<?php

namespace Appart\Modules\LegacyMigration\Application\OwnerReader;

use Appart\Modules\LegacyMigration\Application\OwnerReader\Contract\LegacyMigrationOwnerReaderV1;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationOwnerSource;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationWaveReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveStatusV1;

final readonly class LegacyMigrationWaveOwnerReader implements LegacyMigrationWaveReaderV1
{
    public function __construct(private LegacyMigrationOwnerSource $source, private LegacyMigrationOwnerReaderV1 $policy) {}

    public function read(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationWaveResultV1
    {
        $status = LegacyMigrationWaveStatusV1::from($this->policy->wave($this->source->readWave($subject, $observedAt))->status->value);

        return new LegacyMigrationWaveResultV1($status, $observedAt);
    }
}

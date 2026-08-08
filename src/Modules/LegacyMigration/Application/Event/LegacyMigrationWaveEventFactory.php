<?php

namespace Appart\Modules\LegacyMigration\Application\Event;

use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationWaveReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;

final readonly class LegacyMigrationWaveEventFactory
{
    public function __construct(private LegacyMigrationWaveReaderV1 $reader) {}

    public function create(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationWaveEventV1
    {
        $result = $this->reader->read($subject, $observedAt);

        return new LegacyMigrationWaveEventV1(LegacyMigrationWaveEventType::Observed, new LegacyMigrationWaveEventPayload(LegacyMigrationWaveEventStatus::from($result->status->value), $result->observedAt));
    }
}

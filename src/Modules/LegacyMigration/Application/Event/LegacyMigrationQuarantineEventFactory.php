<?php

namespace Appart\Modules\LegacyMigration\Application\Event;

use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationQuarantineReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;

final readonly class LegacyMigrationQuarantineEventFactory
{
    public function __construct(private LegacyMigrationQuarantineReaderV1 $reader) {}

    public function create(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationQuarantineEventV1
    {
        $result = $this->reader->read($subject, $observedAt);

        return new LegacyMigrationQuarantineEventV1(LegacyMigrationQuarantineEventType::Observed, new LegacyMigrationQuarantineEventPayload(LegacyMigrationQuarantineEventStatus::from($result->status->value), $result->observedAt));
    }
}

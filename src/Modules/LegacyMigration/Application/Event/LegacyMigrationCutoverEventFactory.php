<?php

namespace Appart\Modules\LegacyMigration\Application\Event;

use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationCutoverReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;

final readonly class LegacyMigrationCutoverEventFactory
{
    public function __construct(private LegacyMigrationCutoverReaderV1 $reader) {}

    public function create(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationCutoverEventV1
    {
        $result = $this->reader->read($subject, $observedAt);

        return new LegacyMigrationCutoverEventV1(LegacyMigrationCutoverEventType::Observed, new LegacyMigrationCutoverEventPayload(LegacyMigrationCutoverEventStatus::from($result->status->value), $result->observedAt));
    }
}

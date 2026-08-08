<?php

namespace Appart\Modules\LegacyMigration\Application\Event;

use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationInventoryReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;

final readonly class LegacyMigrationInventoryEventFactory
{
    public function __construct(private LegacyMigrationInventoryReaderV1 $reader) {}

    public function create(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationInventoryEventV1
    {
        $result = $this->reader->read($subject, $observedAt);

        return new LegacyMigrationInventoryEventV1(LegacyMigrationInventoryEventType::Observed, new LegacyMigrationInventoryEventPayload(LegacyMigrationInventoryEventStatus::from($result->status->value), $result->observedAt));
    }
}

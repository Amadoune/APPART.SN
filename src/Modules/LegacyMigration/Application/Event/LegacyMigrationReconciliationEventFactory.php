<?php

namespace Appart\Modules\LegacyMigration\Application\Event;

use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationReconciliationReaderV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;

final readonly class LegacyMigrationReconciliationEventFactory
{
    public function __construct(private LegacyMigrationReconciliationReaderV1 $reader) {}

    public function create(LegacyMigrationSubjectKey $subject, LegacyMigrationObservedAt $observedAt): LegacyMigrationReconciliationEventV1
    {
        $result = $this->reader->read($subject, $observedAt);

        return new LegacyMigrationReconciliationEventV1(LegacyMigrationReconciliationEventType::Observed, new LegacyMigrationReconciliationEventPayload(LegacyMigrationReconciliationEventStatus::from($result->status->value), $result->observedAt));
    }
}

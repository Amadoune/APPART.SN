<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\MaintenanceOperationsReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

final readonly class MaintenanceOperationsEventFactory
{
    public function __construct(private MaintenanceOperationsReaderV1 $reader) {}

    public function create(ReliabilityOperationsObservedAt $observedAt): MaintenanceOperationsEventV1
    {
        $result = $this->reader->read($observedAt);

        return new MaintenanceOperationsEventV1(MaintenanceOperationsEventType::Observed, new MaintenanceOperationsEventPayload(MaintenanceOperationsEventStatus::from($result->status->value), $result->observedAt));
    }
}

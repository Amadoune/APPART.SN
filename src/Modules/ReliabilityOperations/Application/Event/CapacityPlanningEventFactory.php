<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\CapacityPlanningReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

final readonly class CapacityPlanningEventFactory
{
    public function __construct(private CapacityPlanningReaderV1 $reader) {}

    public function create(ReliabilityOperationsObservedAt $observedAt): CapacityPlanningEventV1
    {
        $result = $this->reader->read($observedAt);

        return new CapacityPlanningEventV1(CapacityPlanningEventType::Observed, new CapacityPlanningEventPayload(CapacityPlanningEventStatus::from($result->status->value), $result->observedAt));
    }
}

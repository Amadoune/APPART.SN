<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\AlertingReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

final readonly class AlertingEventFactory
{
    public function __construct(private AlertingReaderV1 $reader) {}

    public function create(ReliabilityOperationsObservedAt $observedAt): AlertingEventV1
    {
        $result = $this->reader->read($observedAt);

        return new AlertingEventV1(AlertingEventType::Observed, new AlertingEventPayload(AlertingEventStatus::from($result->status->value), $result->observedAt));
    }
}

<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\OperationalReadinessReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

final readonly class OperationalReadinessEventFactory
{
    public function __construct(private OperationalReadinessReaderV1 $reader) {}

    public function create(ReliabilityOperationsObservedAt $observedAt): OperationalReadinessEventV1
    {
        $result = $this->reader->read($observedAt);

        return new OperationalReadinessEventV1(OperationalReadinessEventType::Observed, new OperationalReadinessEventPayload(OperationalReadinessEventStatus::from($result->status->value), $result->observedAt));
    }
}

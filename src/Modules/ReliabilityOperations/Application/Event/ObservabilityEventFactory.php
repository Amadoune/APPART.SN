<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ObservabilityReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

final readonly class ObservabilityEventFactory
{
    public function __construct(private ObservabilityReaderV1 $reader) {}

    public function create(ReliabilityOperationsObservedAt $observedAt): ObservabilityEventV1
    {
        $result = $this->reader->read($observedAt);

        return new ObservabilityEventV1(ObservabilityEventType::Observed, new ObservabilityEventPayload(ObservabilityEventStatus::from($result->status->value), $result->observedAt));
    }
}

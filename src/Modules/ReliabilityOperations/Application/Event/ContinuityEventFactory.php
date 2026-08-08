<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ContinuityReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

final readonly class ContinuityEventFactory
{
    public function __construct(private ContinuityReaderV1 $reader) {}

    public function create(ReliabilityOperationsObservedAt $observedAt): ContinuityEventV1
    {
        $result = $this->reader->read($observedAt);

        return new ContinuityEventV1(ContinuityEventType::Observed, new ContinuityEventPayload(ContinuityEventStatus::from($result->status->value), $result->observedAt));
    }
}

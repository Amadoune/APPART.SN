<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ServiceHealthReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

final readonly class ServiceHealthEventFactory
{
    public function __construct(private ServiceHealthReaderV1 $reader) {}

    public function create(ReliabilityOperationsObservedAt $observedAt): ServiceHealthEventV1
    {
        $result = $this->reader->read($observedAt);

        return new ServiceHealthEventV1(ServiceHealthEventType::Observed, new ServiceHealthEventPayload(ServiceHealthEventStatus::from($result->status->value), $result->observedAt));
    }
}

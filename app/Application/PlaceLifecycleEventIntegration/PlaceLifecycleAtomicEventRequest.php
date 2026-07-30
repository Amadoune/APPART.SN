<?php

namespace App\Application\PlaceLifecycleEventIntegration;

use Appart\Modules\Geography\Application\PlaceLifecycleOrchestration\PlaceLifecycleOrchestrationRequest;
use DateTimeImmutable;

final readonly class PlaceLifecycleAtomicEventRequest
{
    public function __construct(
        public PlaceLifecycleOrchestrationRequest $transition,
        public DateTimeImmutable $recordedAt,
    ) {}
}

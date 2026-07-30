<?php

namespace App\Application\ModerationEventDelivery;

use App\Application\ModerationEventDelivery\Contract\ModerationDeliveryStore;
use App\Application\ModerationEventRouting\DeterministicModerationEventRouter;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Application\ModerationRuntime\Contract\ModerationRuntimeV1;
use App\Application\ModerationRuntime\ModerationRuntimeStatus;
use DateTimeImmutable;

final readonly class ModerationDeliveryService
{
    public function __construct(
        private DeterministicModerationEventRouter $router,
        private ModerationDeliveryStore $store,
        private ModerationRuntimeV1 $runtime,
    ) {}

    /** @return array<string, ModerationDeliveryResult> */
    public function publish(ModerationDeliveryMessageV1 $message, DateTimeImmutable $at): array
    {
        $results = [];
        foreach ($this->router->route($message->event) as $destination) {
            $results[$destination->value] = $this->runtime->inspect()->status === ModerationRuntimeStatus::Healthy
                ? $this->store->deliver($message, $destination, $at)
                : ModerationDeliveryResult::Rejected;
        }

        return $results;
    }
}

<?php

namespace Tests\Unit\Application\PublicProjectionWorker\Support;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;

final readonly class FakeConfiguredDeliveryConsumer implements PublicProjectionDeliveryConsumer
{
    public function __construct(private PublicProjectionDeliveryConsumptionResult $result) {}

    public function consume(PublicProjectionDeliveryMessage $message): PublicProjectionDeliveryConsumptionResult
    {
        return $this->result;
    }
}

<?php

namespace Tests\Feature;

use App\Application\MediaIngestionEventDelivery\MediaIngestionDeliveryConsumer;
use App\Application\MediaIngestionEventDelivery\MediaIngestionReplayRetryPolicy;
use App\Application\MediaIngestionEventRouting\DeterministicMediaIngestionEventRouter;
use App\Application\MediaIngestionEventTransport\MediaIngestionEventTransportSerializer;
use Tests\TestCase;

final class MediaIngestionEventDeliveryRuntimeTest extends TestCase
{
    public function test_event_delivery_graph_is_lazy_and_singleton(): void
    {
        foreach ([
            MediaIngestionEventTransportSerializer::class,
            DeterministicMediaIngestionEventRouter::class,
            MediaIngestionDeliveryConsumer::class,
            MediaIngestionReplayRetryPolicy::class,
        ] as $contract) {
            self::assertFalse($this->app->resolved($contract));
            self::assertSame($this->app->make($contract), $this->app->make($contract));
        }
    }
}

<?php

namespace App\Providers;

use App\Application\MediaIngestionEventDelivery\MediaIngestionDeliveryConsumer;
use App\Application\MediaIngestionEventDelivery\MediaIngestionReplayRetryPolicy;
use App\Application\MediaIngestionEventRouting\DeterministicMediaIngestionEventRouter;
use App\Application\MediaIngestionEventTransport\MediaIngestionEventTransportSerializer;
use Illuminate\Support\ServiceProvider;

final class MediaIngestionEventDeliveryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MediaIngestionEventTransportSerializer::class);
        $this->app->singleton(DeterministicMediaIngestionEventRouter::class);
        $this->app->singleton(MediaIngestionDeliveryConsumer::class);
        $this->app->singleton(MediaIngestionReplayRetryPolicy::class);
    }
}

<?php

namespace App\Providers;

use App\Application\ModerationEventDelivery\Contract\ModerationDeliveryStore;
use App\Application\ModerationEventDelivery\ModerationDeliveryService;
use App\Application\ModerationEventRouting\DeterministicModerationEventRouter;
use App\Application\ModerationEventTransport\ModerationEventTransportSerializer;
use App\Infrastructure\ModerationEventDelivery\PostgreSql\PostgreSqlModerationDeliveryStore;
use Illuminate\Support\ServiceProvider;

final class ModerationEventDeliveryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModerationEventTransportSerializer::class);
        $this->app->singleton(DeterministicModerationEventRouter::class);
        $this->app->singleton(PostgreSqlModerationDeliveryStore::class);
        $this->app->alias(PostgreSqlModerationDeliveryStore::class, ModerationDeliveryStore::class);
        $this->app->singleton(ModerationDeliveryService::class);
    }
}

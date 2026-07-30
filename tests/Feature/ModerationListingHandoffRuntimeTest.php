<?php

namespace Tests\Feature;

use App\Application\ModerationListingHandoff\Contract\ModerationListingHandoffResultStore;
use App\Application\ModerationListingHandoff\ModerationListingHandoffConsumer;
use App\Infrastructure\ModerationEventOutbox\PostgreSql\PostgreSqlModerationListingHandoffResultStore;
use PDO;
use Tests\TestCase;

final class ModerationListingHandoffRuntimeTest extends TestCase
{
    public function test_container_resolves_lazy_singleton_consumer_and_result_store(): void
    {
        $this->app->instance(PDO::class, new PDO('sqlite::memory:'));

        $consumer = $this->app->make(ModerationListingHandoffConsumer::class);
        $store = $this->app->make(ModerationListingHandoffResultStore::class);

        self::assertInstanceOf(ModerationListingHandoffConsumer::class, $consumer);
        self::assertInstanceOf(PostgreSqlModerationListingHandoffResultStore::class, $store);
        self::assertSame($consumer, $this->app->make(ModerationListingHandoffConsumer::class));
        self::assertSame($store, $this->app->make(ModerationListingHandoffResultStore::class));
    }
}

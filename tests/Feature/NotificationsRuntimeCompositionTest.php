<?php

namespace Tests\Feature;

use Appart\Modules\Notifications\Application\OwnerSource\NotificationsOwnerSource;
use Appart\Modules\Notifications\Application\Runtime\NotificationsRuntimeV1;
use Appart\Modules\Notifications\Infrastructure\Persistence\NotificationsOwnerSourceMapper;
use Appart\Modules\Notifications\Infrastructure\Persistence\PostgreSql\PostgreSqlNotificationsOwnerSource;
use PDO;
use Tests\TestCase;

final class NotificationsRuntimeCompositionTest extends TestCase
{
    public function test_runtime_binding_is_lazy_unique_and_singleton(): void
    {
        self::assertFalse($this->app->resolved(NotificationsRuntimeV1::class));
        $adapter = new PostgreSqlNotificationsOwnerSource(new PDO('sqlite::memory:'), new NotificationsOwnerSourceMapper);
        $this->app->instance(PostgreSqlNotificationsOwnerSource::class, $adapter);

        self::assertTrue($this->app->bound(NotificationsOwnerSource::class));
        self::assertTrue($this->app->bound(NotificationsRuntimeV1::class));
        self::assertSame($adapter, $this->app->make(NotificationsOwnerSource::class));
        $runtime = $this->app->make(NotificationsRuntimeV1::class);
        self::assertSame($runtime, $this->app->make(NotificationsRuntimeV1::class));
    }
}

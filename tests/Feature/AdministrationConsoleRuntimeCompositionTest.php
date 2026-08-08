<?php

namespace Tests\Feature;

use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationConsoleOwnerSource;
use Appart\Modules\AdministrationConsole\Application\Runtime\AdministrationConsoleRuntimeV1;
use Appart\Modules\AdministrationConsole\Infrastructure\Persistence\AdministrationConsoleOwnerSourceMapper;
use Appart\Modules\AdministrationConsole\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrationConsoleOwnerSource;
use PDO;
use Tests\TestCase;

final class AdministrationConsoleRuntimeCompositionTest extends TestCase
{
    public function test_runtime_binding_is_lazy_unique_and_singleton(): void
    {
        self::assertFalse($this->app->resolved(AdministrationConsoleRuntimeV1::class));
        $adapter = new PostgreSqlAdministrationConsoleOwnerSource(new PDO('sqlite::memory:'), new AdministrationConsoleOwnerSourceMapper);
        $this->app->instance(PostgreSqlAdministrationConsoleOwnerSource::class, $adapter);

        self::assertTrue($this->app->bound(AdministrationConsoleOwnerSource::class));
        self::assertTrue($this->app->bound(AdministrationConsoleRuntimeV1::class));
        self::assertSame($adapter, $this->app->make(AdministrationConsoleOwnerSource::class));
        $runtime = $this->app->make(AdministrationConsoleRuntimeV1::class);
        self::assertSame($runtime, $this->app->make(AdministrationConsoleRuntimeV1::class));
    }
}

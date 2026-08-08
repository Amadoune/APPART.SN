<?php

namespace Tests\Feature;

use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationOwnerSource;
use Appart\Modules\LegacyMigration\Application\Runtime\LegacyMigrationRuntimeV1;
use Appart\Modules\LegacyMigration\Infrastructure\Persistence\LegacyMigrationOwnerSourceMapper;
use Appart\Modules\LegacyMigration\Infrastructure\Persistence\PostgreSql\PostgreSqlLegacyMigrationOwnerSource;
use PDO;
use Tests\TestCase;

final class LegacyMigrationRuntimeCompositionTest extends TestCase
{
    public function test_runtime_binding_is_lazy_nominative_and_singleton(): void
    {
        self::assertFalse($this->app->resolved(LegacyMigrationRuntimeV1::class));
        $adapter = new PostgreSqlLegacyMigrationOwnerSource(new PDO('sqlite::memory:'), new LegacyMigrationOwnerSourceMapper);
        $this->app->instance(PostgreSqlLegacyMigrationOwnerSource::class, $adapter);

        self::assertTrue($this->app->bound(LegacyMigrationOwnerSource::class));
        self::assertTrue($this->app->bound(LegacyMigrationRuntimeV1::class));
        self::assertSame($adapter, $this->app->make(LegacyMigrationOwnerSource::class));
        $runtime = $this->app->make(LegacyMigrationRuntimeV1::class);
        self::assertSame($runtime, $this->app->make(LegacyMigrationRuntimeV1::class));
    }
}

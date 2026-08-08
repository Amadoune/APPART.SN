<?php

namespace Tests\Feature;

use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsOwnerSource;
use Appart\Modules\ReliabilityOperations\Application\Runtime\ReliabilityOperationsRuntimeV1;
use Appart\Modules\ReliabilityOperations\Infrastructure\Persistence\PostgreSql\PostgreSqlReliabilityOperationsOwnerSource;
use Appart\Modules\ReliabilityOperations\Infrastructure\Persistence\ReliabilityOperationsOwnerSourceMapper;
use PDO;
use Tests\TestCase;

final class ReliabilityOperationsRuntimeCompositionTest extends TestCase
{
    public function test_runtime_binding_is_lazy_nominative_and_singleton(): void
    {
        self::assertFalse($this->app->resolved(ReliabilityOperationsRuntimeV1::class));
        $adapter = new PostgreSqlReliabilityOperationsOwnerSource(new PDO('sqlite::memory:'), new ReliabilityOperationsOwnerSourceMapper);
        $this->app->instance(PostgreSqlReliabilityOperationsOwnerSource::class, $adapter);
        self::assertTrue($this->app->bound(ReliabilityOperationsOwnerSource::class));
        self::assertTrue($this->app->bound(ReliabilityOperationsRuntimeV1::class));
        self::assertSame($adapter, $this->app->make(ReliabilityOperationsOwnerSource::class));
        $runtime = $this->app->make(ReliabilityOperationsRuntimeV1::class);
        self::assertSame($runtime, $this->app->make(ReliabilityOperationsRuntimeV1::class));
    }
}

<?php

namespace Tests\Feature;

use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\Contract\SearchOwnerSource;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime\Contract\SearchOwnerSourceRuntimeV1;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchOwnerSource;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchOwnerSourceMapper;
use PDO;
use Tests\TestCase;

final class SearchOwnerSourceRuntimeCompositionTest extends TestCase
{
    public function test_runtime_binding_is_lazy_unique_and_singleton(): void
    {
        self::assertFalse($this->app->resolved(SearchOwnerSourceRuntimeV1::class));
        $adapter = new PostgreSqlSearchOwnerSource(new PDO('sqlite::memory:'), new SearchOwnerSourceMapper);
        $this->app->instance(PostgreSqlSearchOwnerSource::class, $adapter);

        self::assertTrue($this->app->bound(SearchOwnerSource::class));
        self::assertTrue($this->app->bound(SearchOwnerSourceRuntimeV1::class));
        self::assertSame($adapter, $this->app->make(SearchOwnerSource::class));

        $runtime = $this->app->make(SearchOwnerSourceRuntimeV1::class);
        self::assertSame($runtime, $this->app->make(SearchOwnerSourceRuntimeV1::class));
    }
}

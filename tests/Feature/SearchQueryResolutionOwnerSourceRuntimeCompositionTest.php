<?php

namespace Tests\Feature;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\Contract\SearchQueryResolutionOwnerSource;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\Contract\SearchQueryResolutionOwnerSourceRuntimeV1;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchQueryResolutionOwnerSource;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchQueryResolutionOwnerSourceMapper;
use PDO;
use Tests\TestCase;

final class SearchQueryResolutionOwnerSourceRuntimeCompositionTest extends TestCase
{
    public function test_runtime_binding_is_lazy_unique_and_singleton(): void
    {
        self::assertFalse($this->app->resolved(SearchQueryResolutionOwnerSourceRuntimeV1::class));
        $adapter = new PostgreSqlSearchQueryResolutionOwnerSource(new PDO('sqlite::memory:'), new SearchQueryResolutionOwnerSourceMapper);
        $this->app->instance(PostgreSqlSearchQueryResolutionOwnerSource::class, $adapter);

        self::assertTrue($this->app->bound(SearchQueryResolutionOwnerSource::class));
        self::assertTrue($this->app->bound(SearchQueryResolutionOwnerSourceRuntimeV1::class));
        self::assertSame($adapter, $this->app->make(SearchQueryResolutionOwnerSource::class));
        $runtime = $this->app->make(SearchQueryResolutionOwnerSourceRuntimeV1::class);
        self::assertSame($runtime, $this->app->make(SearchQueryResolutionOwnerSourceRuntimeV1::class));
    }
}

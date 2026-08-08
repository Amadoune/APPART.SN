<?php

namespace Tests\Feature;

use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\Contract\SearchOwnerSource;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\Contract\SearchOwnerSourceRuntimeReadV1;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchOwnerSource;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchOwnerSourceMapper;
use PDO;
use Tests\TestCase;

final class SearchOwnerSourceRuntimeReadCompositionTest extends TestCase
{
    public function test_binding_is_lazy_unique_and_singleton(): void
    {
        self::assertFalse($this->app->resolved(SearchOwnerSourceRuntimeReadV1::class));
        $source = new PostgreSqlSearchOwnerSource(new PDO('sqlite::memory:'), new SearchOwnerSourceMapper);
        $this->app->instance(SearchOwnerSource::class, $source);
        self::assertTrue($this->app->bound(SearchOwnerSourceRuntimeReadV1::class));
        $runtimeRead = $this->app->make(SearchOwnerSourceRuntimeReadV1::class);
        self::assertSame($runtimeRead, $this->app->make(SearchOwnerSourceRuntimeReadV1::class));
    }
}

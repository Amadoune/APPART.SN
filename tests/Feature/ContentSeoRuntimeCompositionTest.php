<?php

namespace Tests\Feature;

use Appart\Modules\ContentSeo\Application\OwnerSource\ContentSeoOwnerSource;
use Appart\Modules\ContentSeo\Application\Runtime\ContentSeoRuntimeV1;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\ContentSeoOwnerSourceMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlContentSeoOwnerSource;
use PDO;
use Tests\TestCase;

final class ContentSeoRuntimeCompositionTest extends TestCase
{
    public function test_runtime_binding_is_lazy_unique_and_singleton(): void
    {
        self::assertFalse($this->app->resolved(ContentSeoRuntimeV1::class));
        $adapter = new PostgreSqlContentSeoOwnerSource(new PDO('sqlite::memory:'), new ContentSeoOwnerSourceMapper);
        $this->app->instance(PostgreSqlContentSeoOwnerSource::class, $adapter);

        self::assertTrue($this->app->bound(ContentSeoOwnerSource::class));
        self::assertTrue($this->app->bound(ContentSeoRuntimeV1::class));
        self::assertSame($adapter, $this->app->make(ContentSeoOwnerSource::class));
        $runtime = $this->app->make(ContentSeoRuntimeV1::class);
        self::assertSame($runtime, $this->app->make(ContentSeoRuntimeV1::class));
    }
}

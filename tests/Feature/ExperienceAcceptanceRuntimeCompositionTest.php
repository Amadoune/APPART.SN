<?php

namespace Tests\Feature;

use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceOwnerSource;
use Appart\Modules\ExperienceAcceptance\Application\Runtime\ExperienceAcceptanceRuntimeV1;
use Appart\Modules\ExperienceAcceptance\Infrastructure\Persistence\ExperienceAcceptanceOwnerSourceMapper;
use Appart\Modules\ExperienceAcceptance\Infrastructure\Persistence\PostgreSql\PostgreSqlExperienceAcceptanceOwnerSource;
use PDO;
use Tests\TestCase;

final class ExperienceAcceptanceRuntimeCompositionTest extends TestCase
{
    public function test_runtime_binding_is_lazy_nominative_and_singleton(): void
    {
        self::assertFalse($this->app->resolved(ExperienceAcceptanceRuntimeV1::class));
        $adapter = new PostgreSqlExperienceAcceptanceOwnerSource(new PDO('sqlite::memory:'), new ExperienceAcceptanceOwnerSourceMapper);
        $this->app->instance(PostgreSqlExperienceAcceptanceOwnerSource::class, $adapter);
        self::assertTrue($this->app->bound(ExperienceAcceptanceOwnerSource::class));
        self::assertTrue($this->app->bound(ExperienceAcceptanceRuntimeV1::class));
        self::assertSame($adapter, $this->app->make(ExperienceAcceptanceOwnerSource::class));
        $runtime = $this->app->make(ExperienceAcceptanceRuntimeV1::class);
        self::assertSame($runtime, $this->app->make(ExperienceAcceptanceRuntimeV1::class));
    }
}

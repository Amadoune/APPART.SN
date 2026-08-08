<?php

namespace Tests\Feature;

use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityComplianceOwnerSource;
use Appart\Modules\SecurityCompliance\Application\Runtime\SecurityComplianceRuntimeV1;
use Appart\Modules\SecurityCompliance\Infrastructure\Persistence\PostgreSql\PostgreSqlSecurityComplianceOwnerSource;
use Appart\Modules\SecurityCompliance\Infrastructure\Persistence\SecurityComplianceOwnerSourceMapper;
use PDO;
use Tests\TestCase;

final class SecurityComplianceRuntimeCompositionTest extends TestCase
{
    public function test_runtime_binding_is_lazy_nominative_and_singleton(): void
    {
        self::assertFalse($this->app->resolved(SecurityComplianceRuntimeV1::class));
        $adapter = new PostgreSqlSecurityComplianceOwnerSource(new PDO('sqlite::memory:'), new SecurityComplianceOwnerSourceMapper);
        $this->app->instance(PostgreSqlSecurityComplianceOwnerSource::class, $adapter);
        self::assertTrue($this->app->bound(SecurityComplianceOwnerSource::class));
        self::assertTrue($this->app->bound(SecurityComplianceRuntimeV1::class));
        self::assertSame($adapter, $this->app->make(SecurityComplianceOwnerSource::class));
        $runtime = $this->app->make(SecurityComplianceRuntimeV1::class);
        self::assertSame($runtime, $this->app->make(SecurityComplianceRuntimeV1::class));
    }
}

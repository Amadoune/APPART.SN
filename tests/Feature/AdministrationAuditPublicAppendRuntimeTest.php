<?php

namespace Tests\Feature;

use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\Contract\AdministrationAuditAppendV1;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\AdministrationAuditAppendConnection;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrationAuditAppendV1;
use PDO;
use Tests\TestCase;

final class AdministrationAuditPublicAppendRuntimeTest extends TestCase
{
    public function test_public_append_binding_is_lazy_unique_and_singleton(): void
    {
        $this->app->instance(
            AdministrationAuditAppendConnection::class,
            new AdministrationAuditAppendConnection($this->createStub(PDO::class)),
        );

        self::assertTrue($this->app->bound(AdministrationAuditAppendV1::class));
        self::assertFalse($this->app->resolved(AdministrationAuditAppendV1::class));

        $first = $this->app->make(AdministrationAuditAppendV1::class);
        $second = $this->app->make(AdministrationAuditAppendV1::class);

        self::assertInstanceOf(PostgreSqlAdministrationAuditAppendV1::class, $first);
        self::assertSame($first, $second);
        self::assertSame(
            $first,
            $this->app->make(PostgreSqlAdministrationAuditAppendV1::class),
        );
    }
}

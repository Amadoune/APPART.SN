<?php

namespace Tests\Feature;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\Contract\AntiAbuseOwnerSource;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime\Contract\AntiAbuseOwnerSourceRuntimeV1;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\AntiAbuseOwnerSourceConnection;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlAntiAbuseOwnerSource;
use PDO;
use Tests\TestCase;

final class ContactsLeadsAntiAbuseOwnerSourceRuntimeCompositionTest extends TestCase
{
    public function test_owner_runtime_bindings_are_unique_lazy_singletons(): void
    {
        self::assertFalse($this->app->resolved(AntiAbuseOwnerSourceRuntimeV1::class));
        $this->app->instance(AntiAbuseOwnerSourceConnection::class, new AntiAbuseOwnerSourceConnection(new PDO('sqlite::memory:')));

        self::assertTrue($this->app->bound(AntiAbuseOwnerSource::class));
        self::assertTrue($this->app->bound(AntiAbuseOwnerSourceRuntimeV1::class));

        $source = $this->app->make(AntiAbuseOwnerSource::class);
        $runtime = $this->app->make(AntiAbuseOwnerSourceRuntimeV1::class);

        self::assertInstanceOf(PostgreSqlAntiAbuseOwnerSource::class, $source);
        self::assertSame($source, $this->app->make(AntiAbuseOwnerSource::class));
        self::assertSame($runtime, $this->app->make(AntiAbuseOwnerSourceRuntimeV1::class));
    }
}

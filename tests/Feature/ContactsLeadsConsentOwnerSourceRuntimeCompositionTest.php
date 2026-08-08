<?php

namespace Tests\Feature;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\Contract\ConsentOwnerSource;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime\Contract\ConsentOwnerSourceRuntimeV1;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\ConsentOwnerSourceConnection;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlConsentOwnerSource;
use PDO;
use Tests\TestCase;

final class ContactsLeadsConsentOwnerSourceRuntimeCompositionTest extends TestCase
{
    public function test_owner_runtime_bindings_are_unique_lazy_singletons(): void
    {
        $this->app->instance(ConsentOwnerSourceConnection::class, new ConsentOwnerSourceConnection(new PDO('sqlite::memory:')));

        self::assertTrue($this->app->bound(ConsentOwnerSource::class));
        self::assertTrue($this->app->bound(ConsentOwnerSourceRuntimeV1::class));

        $source = $this->app->make(ConsentOwnerSource::class);
        $runtime = $this->app->make(ConsentOwnerSourceRuntimeV1::class);

        self::assertInstanceOf(PostgreSqlConsentOwnerSource::class, $source);
        self::assertSame($source, $this->app->make(ConsentOwnerSource::class));
        self::assertSame($runtime, $this->app->make(ConsentOwnerSourceRuntimeV1::class));
    }
}

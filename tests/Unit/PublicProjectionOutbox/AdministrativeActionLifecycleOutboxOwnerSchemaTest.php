<?php

namespace Tests\Unit\PublicProjectionOutbox;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxSchema;
use PHPUnit\Framework\TestCase;

final class AdministrativeActionLifecycleOutboxOwnerSchemaTest extends TestCase
{
    public function test_administration_audit_owner_resolves_bidirectionally(): void
    {
        $module = PublicProjectionDeliverySourceModule::fromString('AdministrationAudit');

        self::assertSame('administration_audit', PostgreSqlPublicProjectionOutboxSchema::for($module));
        self::assertSame('AdministrationAudit', PostgreSqlPublicProjectionOutboxSchema::moduleFor('administration_audit')->value);
        self::assertContains('administration_audit', PostgreSqlPublicProjectionOutboxSchema::all());
        self::assertSame(11, count(PostgreSqlPublicProjectionOutboxSchema::all()));
    }
}

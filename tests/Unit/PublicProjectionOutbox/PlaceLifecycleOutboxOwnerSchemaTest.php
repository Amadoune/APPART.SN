<?php

namespace Tests\Unit\PublicProjectionOutbox;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxSchema;
use PHPUnit\Framework\TestCase;

final class PlaceLifecycleOutboxOwnerSchemaTest extends TestCase
{
    public function test_geography_owner_resolves_bidirectionally_without_replacing_historical_owners(): void
    {
        $module = PublicProjectionDeliverySourceModule::fromString('Geography');

        self::assertSame('geography', PostgreSqlPublicProjectionOutboxSchema::for($module));
        self::assertSame('Geography', PostgreSqlPublicProjectionOutboxSchema::moduleFor('geography')->value);
        self::assertContains('geography', PostgreSqlPublicProjectionOutboxSchema::all());
        self::assertCount(11, PostgreSqlPublicProjectionOutboxSchema::all());
    }
}

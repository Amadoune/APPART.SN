<?php

namespace Tests\Unit\PublicProjectionOutbox;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxSchema;
use PHPUnit\Framework\TestCase;

final class MediaItemLifecycleOutboxOwnerSchemaTest extends TestCase
{
    public function test_media_owner_resolves_bidirectionally_without_a_parallel_resolver(): void
    {
        $module = PublicProjectionDeliverySourceModule::fromString('Media');

        self::assertSame('media', PostgreSqlPublicProjectionOutboxSchema::for($module));
        self::assertSame('Media', PostgreSqlPublicProjectionOutboxSchema::moduleFor('media')->value);
        self::assertContains('media', PostgreSqlPublicProjectionOutboxSchema::all());
        self::assertSame(11, count(PostgreSqlPublicProjectionOutboxSchema::all()));
    }
}

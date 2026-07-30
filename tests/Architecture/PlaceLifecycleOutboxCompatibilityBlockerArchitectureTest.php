<?php

namespace Tests\Architecture;

use App\Application\PlaceLifecycleEventConsumption\PlaceLifecycleDeliveryConsumer;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleDeliveryPayload;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use PHPUnit\Framework\TestCase;

final class PlaceLifecycleOutboxCompatibilityBlockerArchitectureTest extends TestCase
{
    public function test_certified_place_contracts_are_generic_delivery_contracts(): void
    {
        self::assertTrue(is_a(
            PlaceLifecycleDeliveryPayload::class,
            PublicProjectionDeliveryPayload::class,
            true,
        ));
        self::assertTrue(is_a(
            PlaceLifecycleDeliveryConsumer::class,
            PublicProjectionDeliveryConsumer::class,
            true,
        ));
    }

    public function test_geography_outbox_implementation_is_opened_after_the_amendment(): void
    {
        $root = dirname(__DIR__, 2);

        self::assertFileExists(
            $root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/040_geography_outbox_owner.sql',
        );
        self::assertStringContainsString(
            "'Geography' => 'geography'",
            (string) file_get_contents(
                $root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlPublicProjectionOutboxSchema.php',
            ),
        );
    }
}

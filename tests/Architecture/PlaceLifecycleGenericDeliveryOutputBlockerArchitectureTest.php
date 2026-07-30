<?php

namespace Tests\Architecture;

use App\Application\PlaceLifecycleEventConsumption\PlaceLifecycleDeliveryConsumer;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleDeliveryPayload;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class PlaceLifecycleGenericDeliveryOutputBlockerArchitectureTest extends TestCase
{
    public function test_checksum_and_consumer_result_types_are_compatible_after_r3(): void
    {
        self::assertSame(
            'string',
            (string) (new ReflectionMethod(PublicProjectionDeliveryPayload::class, 'checksum'))->getReturnType(),
        );
        self::assertSame(
            'string',
            (string) (new ReflectionMethod(PlaceLifecycleDeliveryPayload::class, 'checksum'))->getReturnType(),
        );
        self::assertSame(
            'App\\Application\\PublicProjectionDelivery\\PublicProjectionDeliveryConsumptionResult',
            (string) (new ReflectionMethod(PublicProjectionDeliveryConsumer::class, 'consume'))->getReturnType(),
        );
        self::assertSame(
            'App\\Application\\PublicProjectionDelivery\\PublicProjectionDeliveryConsumptionResult',
            (string) (new ReflectionMethod(PlaceLifecycleDeliveryConsumer::class, 'consume'))->getReturnType(),
        );
    }

    public function test_4_8_j_infrastructure_is_opened_after_r3(): void
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

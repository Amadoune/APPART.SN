<?php

namespace Tests\Feature;

use App\Application\AccountStatusEventConsumption\AccountStatusDeliveryConsumer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryConsumerRegistry;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryMode;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventType;
use PDO;
use ReflectionProperty;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class AccountStatusOutboxCompatibilityRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_registry_declares_two_account_status_events_as_routed_v1(): void
    {
        $registry = $this->app->make(PublicProjectionDeliveryConsumerRegistry::class);
        $registrations = (new ReflectionProperty($registry, 'registrations'))->getValue($registry);
        self::assertIsArray($registrations);

        foreach (AccountStatusEventType::cases() as $eventType) {
            $matching = array_values(array_filter(
                $registrations,
                static fn ($registration): bool => $registration->eventType == PublicProjectionDeliveryEventType::fromString($eventType->value)
                    && $registration->payloadVersion == PublicProjectionDeliveryPayloadVersion::fromInt(1),
            ));

            self::assertCount(1, $matching);
            self::assertInstanceOf(AccountStatusDeliveryConsumer::class, $matching[0]->consumer);
            self::assertSame(PublicProjectionDeliveryMode::RoutedV1, $matching[0]->mode);
        }
    }
}

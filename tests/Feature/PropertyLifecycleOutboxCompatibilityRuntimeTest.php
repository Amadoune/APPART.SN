<?php

namespace Tests\Feature;

use App\Application\PropertyLifecycleEventConsumer\PropertyLifecycleEventDeliveryConsumer;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryConsumerRegistry;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventType;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\PublicProjectionAuthorizedDeliveryCatalog;
use Tests\TestCase;

final class PropertyLifecycleOutboxCompatibilityRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_consumer_is_bound_lazily_as_one_production_singleton(): void
    {
        self::assertTrue($this->app->bound(PropertyLifecycleEventDeliveryConsumer::class));
        self::assertFalse($this->app->resolved(PropertyLifecycleEventDeliveryConsumer::class));

        $consumer = $this->app->make(PropertyLifecycleEventDeliveryConsumer::class);

        self::assertSame($consumer, $this->app->make(PropertyLifecycleEventDeliveryConsumer::class));
        self::assertStringNotContainsStringIgnoringCase('fake', $consumer::class);
    }

    public function test_worker_registry_preserves_property_entries_in_the_complete_registry(): void
    {
        $registry = $this->app->make(PublicProjectionDeliveryConsumerRegistry::class);
        $registrations = new \ReflectionProperty($registry, 'registrations')->getValue($registry);

        self::assertIsArray($registrations);
        PublicProjectionAuthorizedDeliveryCatalog::assertMatches($registrations);
        $pairs = array_map(static fn ($registration): string => $registration->eventType->value.'@'.$registration->payloadVersion->value, $registrations);
        foreach (PropertyLifecycleEventType::cases() as $eventType) {
            self::assertContains($eventType->value.'@1', $pairs);
        }
    }
}

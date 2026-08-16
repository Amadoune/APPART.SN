<?php

namespace Tests\Feature;

use App\Application\ProfessionalStatusEventConsumption\ProfessionalStatusDeliveryConsumer;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryConsumerRegistry;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventType;
use PDO;
use ReflectionProperty;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\PublicProjectionAuthorizedDeliveryCatalog;
use Tests\TestCase;

final class ProfessionalStatusOutboxCompatibilityRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_registry_contains_forty_three_unique_pairs_and_two_professional_consumers(): void
    {
        $registry = $this->app->make(PublicProjectionDeliveryConsumerRegistry::class);
        $registrations = (new ReflectionProperty($registry, 'registrations'))->getValue($registry);
        self::assertIsArray($registrations);
        PublicProjectionAuthorizedDeliveryCatalog::assertMatches($registrations);
        foreach (ProfessionalStatusEventType::cases() as $eventType) {
            $matching = array_values(array_filter($registrations, static fn ($registration): bool => $registration->eventType->value === $eventType->value));
            self::assertCount(1, $matching);
            self::assertInstanceOf(ProfessionalStatusDeliveryConsumer::class, $matching[0]->consumer);
        }
    }

    public function test_runtime_health_remains_healthy_at_forty(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();
        self::assertGreaterThanOrEqual(55, count(PublicProjectionRuntimeRequirements::certified()));
        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
    }
}

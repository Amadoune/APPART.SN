<?php

namespace Tests\Feature;

use App\Application\ProfessionalStatusEventConsumption\ProfessionalStatusDeliveryConsumptionPolicy;
use App\Application\ProfessionalStatusEventRouting\DurableProfessionalStatusEventRouter;
use App\Application\ProfessionalStatusEventRouting\ProfessionalStatusInboxStore;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRouter;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusTransportSerializer;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use App\Infrastructure\ProfessionalStatusEventRouting\PostgreSql\PostgreSqlProfessionalStatusInboxRepository;
use PDO;
use ReflectionProperty;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class ProfessionalStatusEventRoutingRuntimeCompositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_production_graph_is_unique_lazy_and_resolved(): void
    {
        foreach ([ProfessionalStatusTransportSerializer::class, PostgreSqlProfessionalStatusInboxRepository::class, ProfessionalStatusInboxStore::class, DurableProfessionalStatusEventRouter::class, ProfessionalStatusEventRouter::class, ProfessionalStatusDeliveryConsumptionPolicy::class] as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }
        $serializer = $this->app->make(ProfessionalStatusTransportSerializer::class);
        $repository = $this->app->make(PostgreSqlProfessionalStatusInboxRepository::class);
        $store = $this->app->make(ProfessionalStatusInboxStore::class);
        $implementation = $this->app->make(DurableProfessionalStatusEventRouter::class);
        $router = $this->app->make(ProfessionalStatusEventRouter::class);
        self::assertSame($repository, $store);
        self::assertSame($implementation, $router);
        self::assertSame($store, (new ReflectionProperty($implementation, 'store'))->getValue($implementation));
        self::assertSame($this->app->make(PDO::class), (new ReflectionProperty($repository, 'connection'))->getValue($repository));
        self::assertSame($serializer, (new ReflectionProperty($repository, 'serializer'))->getValue($repository));
    }

    public function test_runtime_health_is_structural_and_healthy_at_forty(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();
        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
        self::assertGreaterThanOrEqual(55, count(PublicProjectionRuntimeRequirements::certified()));
    }
}

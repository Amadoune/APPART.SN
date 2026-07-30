<?php

namespace Tests\Feature;

use App\Application\ListingPublicationEventConsumer\ListingPublicationEventDeliveryConsumer;
use App\Application\ListingPublicationEventIntegration\AtomicListingPublicationEventOrchestrator;
use App\Application\ListingPublicationEventIntegration\Contract\ListingPublicationAtomicTransaction;
use App\Application\ListingPublicationEventIntegration\Contract\ListingPublicationEventOrchestrator;
use App\Application\ListingPublicationEventRouting\Contract\ListingPublicationEventDestination;
use App\Application\ListingPublicationEventRouting\DurableListingPublicationEventRouter;
use App\Application\ListingPublicationEventTransport\Contract\ListingPublicationEventRouter;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use App\Infrastructure\ListingPublicationEventRouting\PostgreSql\PostgreSqlListingPublicationEventInbox;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventSerializer;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class ListingPublicationEventRoutingRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_production_routing_graph_is_lazy_unique_and_fully_resolved(): void
    {
        $components = [
            ListingPublicationEventSerializer::class,
            PostgreSqlListingPublicationEventInbox::class,
            ListingPublicationEventDestination::class,
            DurableListingPublicationEventRouter::class,
            ListingPublicationEventRouter::class,
        ];
        foreach ($components as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $inbox = $this->app->make(PostgreSqlListingPublicationEventInbox::class);
        $destination = $this->app->make(ListingPublicationEventDestination::class);
        $implementation = $this->app->make(DurableListingPublicationEventRouter::class);
        $router = $this->app->make(ListingPublicationEventRouter::class);

        self::assertSame($inbox, $destination);
        self::assertSame($implementation, $router);
        self::assertSame($destination, new \ReflectionProperty($implementation, 'destination')->getValue($implementation));
        self::assertSame($this->app->make(PDO::class), new \ReflectionProperty($inbox, 'connection')->getValue($inbox));
        self::assertSame($this->app->make(ListingPublicationEventSerializer::class), new \ReflectionProperty($inbox, 'serializer')->getValue($inbox));
        self::assertStringNotContainsStringIgnoringCase('fake', $implementation::class);
        self::assertStringNotContainsStringIgnoringCase('null', $implementation::class);
    }

    public function test_runtime_health_remains_healthy_with_the_durable_route(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
    }

    public function test_listing_publication_transport_consumer_is_lazy_and_uses_the_certified_router(): void
    {
        self::assertTrue($this->app->bound(ListingPublicationEventDeliveryConsumer::class));
        self::assertFalse($this->app->resolved(ListingPublicationEventDeliveryConsumer::class));

        $consumer = $this->app->make(ListingPublicationEventDeliveryConsumer::class);

        self::assertSame($consumer, $this->app->make(ListingPublicationEventDeliveryConsumer::class));
        self::assertSame($this->app->make(ListingPublicationEventRouter::class), new \ReflectionProperty($consumer, 'router')->getValue($consumer));
    }

    public function test_atomic_event_orchestrator_is_a_lazy_unique_production_alias(): void
    {
        foreach ([ListingPublicationAtomicTransaction::class, AtomicListingPublicationEventOrchestrator::class, ListingPublicationEventOrchestrator::class] as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $implementation = $this->app->make(AtomicListingPublicationEventOrchestrator::class);
        $orchestrator = $this->app->make(ListingPublicationEventOrchestrator::class);

        self::assertSame($implementation, $orchestrator);
        self::assertSame($this->app->make(ListingPublicationAtomicTransaction::class), new \ReflectionProperty($implementation, 'transaction')->getValue($implementation));
        self::assertStringNotContainsStringIgnoringCase('fake', $implementation::class);
        self::assertStringNotContainsStringIgnoringCase('null', $implementation::class);
    }
}

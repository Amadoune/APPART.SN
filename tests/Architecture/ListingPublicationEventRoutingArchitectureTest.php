<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ListingPublicationEventRoutingArchitectureTest extends TestCase
{
    public function test_one_router_and_one_durable_destination_are_composed(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertIsString($provider);
        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlListingPublicationEventInbox::class)'));
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlListingPublicationEventInbox::class, ListingPublicationEventDestination::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(DurableListingPublicationEventRouter::class)'));
        self::assertSame(1, substr_count($provider, 'alias(DurableListingPublicationEventRouter::class, ListingPublicationEventRouter::class)'));
        self::assertSame([], glob($root.'/app/Providers/*ListingPublicationEvent*') ?: []);
    }

    public function test_router_contains_no_workflow_transition_http_or_projection_logic(): void
    {
        $router = file_get_contents(dirname(__DIR__, 2).'/app/Application/ListingPublicationEventRouting/DurableListingPublicationEventRouter.php');
        self::assertIsString($router);
        self::assertStringContainsString('$this->destination->transfer($event)', $router);
        foreach (['ListingPublicationWorkflow', 'ListingPublicationTransition', '->decide(', 'ListingPublicationState::', 'ListingPublicationAction::', 'Http', 'Projection', 'Outbox'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $router);
        }
    }

    public function test_inbox_is_the_only_new_routing_infrastructure_and_has_no_business_decision(): void
    {
        $root = dirname(__DIR__, 2).'/app/Infrastructure/ListingPublicationEventRouting';
        $php = glob($root.'/PostgreSql/*.php') ?: [];
        self::assertCount(1, $php);
        $contents = file_get_contents($php[0]);
        self::assertIsString($contents);
        foreach (['ListingPublicationWorkflow', 'ListingPublicationTransition', '->decide(', 'ListingPublicationAction::', 'Http', 'Projection'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_bootstrap_constructs_but_never_executes_the_route(): void
    {
        $provider = file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertIsString($provider);
        foreach (['->route(', '->transfer(', 'canonical_event', 'INSERT INTO listing_lifecycle.publication_event_inbox'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    public function test_runtime_health_inspects_both_routing_capabilities_without_transfer(): void
    {
        $requirements = file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        self::assertIsString($requirements);
        self::assertStringContainsString('RuntimeHealthComponent::ListingPublicationEventDestination, ListingPublicationEventDestination::class', $requirements);
        self::assertStringContainsString('RuntimeHealthComponent::ListingPublicationEventRouter, ListingPublicationEventRouter::class', $requirements);
        self::assertStringNotContainsString('->route(', $requirements);
        self::assertStringNotContainsString('->transfer(', $requirements);
    }
}

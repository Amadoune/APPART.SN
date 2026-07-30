<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PropertyLifecycleEventRoutingArchitectureTest extends TestCase
{
    public function test_router_contains_only_the_closed_destination_mapping(): void
    {
        $router = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/PropertyLifecycleEventRouting/DurablePropertyLifecycleEventRouter.php');
        self::assertSame(1, substr_count($router, '$this->destination->transfer('));
        self::assertSame(5, substr_count($router, 'PropertyLifecycleEventDestinationStatus::'));
        self::assertStringNotContainsString('default', $router);
        foreach (['PropertyLifecycleWorkflow', 'PropertyLifecycleTransition', '->decide(', 'PDO', 'PostgreSql'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $router);
        }
    }

    public function test_routing_has_no_http_outbox_worker_consumer_projection_or_event_bus(): void
    {
        foreach ([
            dirname(__DIR__, 2).'/app/Application/PropertyLifecycleEventRouting',
            dirname(__DIR__, 2).'/app/Infrastructure/PropertyLifecycleEventRouting',
        ] as $root) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                $contents = (string) file_get_contents($file->getPathname());
                foreach (['Http', 'Outbox', 'Worker', 'Consumer', 'Projection', 'EventBus', 'HandlerRegistry'] as $forbidden) {
                    self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
                }
            }
        }
    }

    public function test_runtime_has_one_lazy_destination_and_router_alias_without_execution(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlPropertyLifecycleEventInbox::class, PropertyLifecycleEventDestination::class)'));
        self::assertSame(1, substr_count($provider, 'alias(DurablePropertyLifecycleEventRouter::class, PropertyLifecycleEventRouter::class)'));
        foreach (['->transfer(', '->route(', '->prepare(', '->query(', '->beginTransaction('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    public function test_runtime_health_only_inspects_routing_contracts(): void
    {
        $requirements = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        self::assertStringContainsString('RuntimeHealthComponent::PropertyLifecycleEventDestination, PropertyLifecycleEventDestination::class', $requirements);
        self::assertStringContainsString('RuntimeHealthComponent::PropertyLifecycleEventRouter, PropertyLifecycleEventRouter::class', $requirements);
        self::assertStringNotContainsString('PostgreSqlPropertyLifecycleEventInbox', $requirements);
        self::assertStringNotContainsString('DurablePropertyLifecycleEventRouter', $requirements);
    }
}

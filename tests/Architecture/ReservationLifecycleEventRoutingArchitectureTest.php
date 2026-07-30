<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ReservationLifecycleEventRoutingArchitectureTest extends TestCase
{
    public function test_router_is_the_unique_implementation_of_the_certified_port(): void
    {
        $root = dirname(__DIR__, 2);
        $implementations = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/app')) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $contents = (string) file_get_contents($file->getPathname());
                if (str_contains($contents, 'implements ReservationLifecycleEventRouterPort')) {
                    $implementations[] = $file->getPathname();
                }
            }
        }

        self::assertCount(1, $implementations);
        self::assertStringEndsWith('DeterministicReservationLifecycleEventRouter.php', $implementations[0]);
    }

    public function test_router_only_validates_the_envelope_and_calls_the_store_once(): void
    {
        $router = (string) file_get_contents($this->applicationRoot().'/DeterministicReservationLifecycleEventRouter.php');

        self::assertSame(1, substr_count($router, '$this->store->store('));
        self::assertSame(1, substr_count($router, 'isContractuallyCoherent(') - 1);
        self::assertStringContainsString('ReservationLifecycleRoutingStatus::CorruptedEnvelope', $router);
        self::assertStringContainsString('ReservationLifecycleRoutingStatus::PersistenceCorrupted', $router);
        self::assertStringNotContainsString('default', $router);
        foreach (['ReservationLifecycleWorkflow', 'ReservationLifecycleTransition', 'ReservationLifecycleEventCatalog', '->decide(', 'eventFor(', 'PDO', 'PostgreSql'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $router);
        }
    }

    public function test_routing_has_no_http_outbox_worker_consumer_projection_or_publication(): void
    {
        foreach ([$this->applicationRoot(), $this->infrastructureRoot()] as $root) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
                if (! $file->isFile() || ! in_array($file->getExtension(), ['php', 'sql'], true)) {
                    continue;
                }
                $contents = (string) file_get_contents($file->getPathname());
                foreach (['Http', 'Outbox', 'Worker', 'Consumer', 'Projection', 'EventBus', 'RabbitMQ', 'Kafka', 'publish(', 'dispatch('] as $forbidden) {
                    self::assertStringNotContainsStringIgnoringCase($forbidden, $contents, $file->getPathname());
                }
            }
        }
    }

    public function test_postgresql_slice_contains_no_business_rule_trigger_or_transition_matrix(): void
    {
        $repository = (string) file_get_contents($this->infrastructureRoot().'/PostgreSql/PostgreSqlReservationLifecycleInboxRepository.php');
        $migration = (string) file_get_contents($this->infrastructureRoot().'/PostgreSql/Migrations/020_reservation_lifecycle_event_inbox.sql');

        foreach (['ReservationLifecycleWorkflow', 'ReservationLifecycleAction', 'ReservationLifecycleState', 'ReservationLifecycleTransition', '->decide(', 'eventFor('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $repository);
            self::assertStringNotContainsString($forbidden, $migration);
        }
        self::assertStringNotContainsStringIgnoringCase('trigger', $migration);
        self::assertStringContainsString('message_id text NOT NULL UNIQUE', $migration);
        self::assertStringContainsString('reservation_lifecycle_event_inbox_restore_lookup', $migration);
    }

    public function test_routing_is_composed_only_by_the_explicit_runtime_amendment(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        $requirements = (string) file_get_contents($root.'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');

        self::assertStringContainsString('alias(PostgreSqlReservationLifecycleInboxRepository::class, ReservationLifecycleInboxStore::class)', $provider);
        self::assertStringContainsString('alias(DeterministicReservationLifecycleEventRouter::class, ReservationLifecycleEventRouterPort::class)', $provider);
        self::assertStringContainsString('ReservationLifecycleInboxStore::class', $requirements);
        self::assertStringContainsString('ReservationLifecycleEventRouterPort::class', $requirements);
        self::assertStringNotContainsString('PostgreSqlReservationLifecycleInboxRepository', $requirements);
        self::assertStringNotContainsString('DeterministicReservationLifecycleEventRouter', $requirements);
    }

    private function applicationRoot(): string
    {
        return dirname(__DIR__, 2).'/app/Application/ReservationLifecycleEventRouting';
    }

    private function infrastructureRoot(): string
    {
        return dirname(__DIR__, 2).'/app/Infrastructure/ReservationLifecycleEventRouting';
    }
}

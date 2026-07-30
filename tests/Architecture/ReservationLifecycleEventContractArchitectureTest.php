<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ReservationLifecycleEventContractArchitectureTest extends TestCase
{
    public function test_event_contract_is_application_only_and_technology_free(): void
    {
        $files = glob($this->eventRoot().'/*.php') ?: [];
        self::assertCount(10, $files);
        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['Illuminate', 'Laravel', 'PDO', 'PostgreSql', '\\Infrastructure\\', 'Outbox', 'Worker', 'Consumer', 'Projection', 'Http', 'RuntimeHealth', 'Repository'] as $forbidden) {
                self::assertStringNotContainsStringIgnoringCase($forbidden, $contents, $file);
            }
            self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents, $file);
        }
    }

    public function test_catalog_contains_exactly_eleven_explicit_bijective_mappings_without_workflow_consultation(): void
    {
        $catalog = (string) file_get_contents($this->eventRoot().'/ReservationLifecycleEventCatalog.php');
        self::assertSame(11, substr_count($catalog, "' => ReservationLifecycleEventType::"));
        self::assertSame(11, count(array_unique($this->mappedEventCases($catalog))));
        self::assertStringNotContainsString('ReservationLifecycleWorkflow', $catalog);
        self::assertStringNotContainsString('->decide(', $catalog);
        self::assertStringNotContainsString('Initialized', $catalog);
    }

    public function test_identity_and_contract_have_no_clock_random_or_technical_transport_source(): void
    {
        foreach (glob($this->eventRoot().'/*.php') ?: [] as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['now()', 'new DateTime', 'CURRENT_TIMESTAMP', 'clock_timestamp', 'microtime(', 'time()', 'random', 'uuid(', 'DeliveryPayload', 'EventRouter', 'Inbox'] as $forbidden) {
                self::assertStringNotContainsStringIgnoringCase($forbidden, $contents, $file);
            }
        }
    }

    public function test_contract_is_not_wired_to_orchestration_and_runtime_only_enumerates_its_closed_types(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        $orchestrator = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Application/ReservationLifecycleOrchestration/DeterministicReservationLifecycleEventOrchestrator.php');

        self::assertStringNotContainsString('ReservationLifecycleEventCatalog', $provider);
        self::assertStringContainsString('ReservationLifecycleEventType::cases()', $provider);
        self::assertStringNotContainsString('ReservationLifecycleEventCatalog', $orchestrator);
        self::assertStringNotContainsString('ReservationLifecycleEventType', $orchestrator);
    }

    /** @return list<string> */
    private function mappedEventCases(string $catalog): array
    {
        preg_match_all("/' => ReservationLifecycleEventType::([A-Za-z]+)/", $catalog, $matches);

        return $matches[1];
    }

    private function eventRoot(): string
    {
        return dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Application/ReservationLifecycleEvent';
    }
}

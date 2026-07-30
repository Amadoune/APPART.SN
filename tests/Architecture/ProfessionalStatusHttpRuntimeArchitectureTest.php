<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ProfessionalStatusHttpRuntimeArchitectureTest extends TestCase
{
    public function test_the_controller_is_only_an_atomic_http_adapter(): void
    {
        $source = $this->source('app/Http/Controllers/ProfessionalStatusHttpController.php');

        self::assertStringContainsString('ProfessionalStatusAtomicEventOrchestrator', $source);
        self::assertSame(1, substr_count($source, '->transition('));
        self::assertStringNotContainsString('ProfessionalStatusWorkflow', $source);
        self::assertStringNotContainsString('ProfessionalStatusWorkflowStore', $source);
        self::assertStringNotContainsString('ProfessionalStatusContextualTransitionStore', $source);
        self::assertStringNotContainsString('PublicProjectionOutbox', $source);
        self::assertStringNotContainsString('PDO', $source);
    }

    public function test_the_request_contains_only_closed_transport_translation(): void
    {
        $source = $this->source('app/Http/Requests/ProfessionalStatusTransitionRequest.php');

        self::assertStringContainsString('ProfessionalStatusAction::Suspend', $source);
        self::assertStringContainsString('ProfessionalStatusAction::Reactivate', $source);
        self::assertStringNotContainsString('ProfessionalStatusAction::Unknown->value', $source);
        self::assertStringNotContainsString('ProfessionalStatusWorkflow', $source);
        self::assertStringNotContainsString('now(', $source);
        self::assertStringNotContainsString('random_', $source);
        self::assertStringNotContainsString('ListingCatalog', $source);
        self::assertStringNotContainsString('AdvertiserCatalog', $source);
    }

    public function test_the_mapper_exhaustively_names_all_eight_results_without_default(): void
    {
        $source = $this->source('app/Http/ProfessionalStatusHttpResultMapper.php');

        foreach (['Applied', 'AlreadyApplied', 'Missing', 'VersionConflict', 'Denied', 'StateConflict', 'ContextDivergence', 'PersistenceCorrupted'] as $status) {
            self::assertSame(1, substr_count($source, "ProfessionalStatusOrchestrationStatus::$status"));
        }

        self::assertStringNotContainsString('default', $source);
    }

    private function source(string $path): string
    {
        $source = file_get_contents(dirname(__DIR__, 2).'/'.$path);
        self::assertIsString($source);

        return $source;
    }
}

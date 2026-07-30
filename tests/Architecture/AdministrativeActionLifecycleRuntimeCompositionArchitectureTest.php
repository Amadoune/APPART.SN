<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AdministrativeActionLifecycleRuntimeCompositionArchitectureTest extends TestCase
{
    public function test_existing_runtime_root_contains_one_lazy_graph(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertSame(1, substr_count($provider, 'singleton(AdministrativeActionLifecycleWorkflow::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(AdministrativeActionLifecycleWorkflowMapper::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(AdministrativeActionEnrollmentCanonicalizer::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlAdministrativeActionLifecycleAtomicPersistenceTransaction::class)'));
        self::assertSame(1, substr_count($provider, "singleton(\n            PostgreSqlAdministrativeActionLifecycleRepository::class,"));
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlAdministrativeActionLifecycleRepository::class, AdministrativeActionLifecycleWorkflowStore::class)'));
        self::assertSame([], glob($root.'/app/Providers/*AdministrativeActionLifecycle*') ?: []);
    }

    public function test_bootstrap_executes_no_workflow_storage_transaction_or_sql(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        foreach (['->decide(', '->enroll(', '->append(', '->read(', '->run(', '->beginTransaction('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
        self::assertDoesNotMatchRegularExpression('/\b(?:SELECT|INSERT|UPDATE|DELETE)\b/', $provider);
    }

    public function test_runtime_health_exposes_only_the_two_application_capabilities(): void
    {
        $requirements = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        self::assertStringContainsString('RuntimeHealthComponent::AdministrativeActionLifecycleWorkflow, AdministrativeActionLifecycleWorkflow::class', $requirements);
        self::assertStringContainsString('RuntimeHealthComponent::AdministrativeActionLifecycleWorkflowStore, AdministrativeActionLifecycleWorkflowStore::class', $requirements);
        foreach (['PostgreSqlAdministrativeActionLifecycleRepository', 'AdministrativeActionLifecycleWorkflowMapper', 'AdministrativeActionEnrollmentCanonicalizer', '->decide(', '->read(', '->append('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $requirements);
        }
    }

    public function test_composition_introduces_no_unauthorized_layer_or_runtime_behavior(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        $lines = array_filter(explode("\n", $provider), static fn (string $line): bool => str_contains($line, 'AdministrativeActionLifecycle')
            || str_contains($line, 'AdministrativeActionEnrollmentCanonicalizer'));
        $composition = implode("\n", $lines);
        foreach (['Http', 'Worker', 'Fake', 'Null', 'Fallback'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $composition);
        }
    }
}

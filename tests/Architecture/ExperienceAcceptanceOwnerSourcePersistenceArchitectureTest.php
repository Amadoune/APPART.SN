<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ExperienceAcceptanceOwnerSourcePersistenceArchitectureTest extends TestCase
{
    public function test_application_owner_source_is_owner_scoped_and_infrastructure_free(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ExperienceAcceptance/Application/OwnerSource';
        $files = glob($root.'/*.php') ?: [];
        self::assertCount(7, $files);
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), $files));
        foreach (['responsive_compliance', 'accessibility_compliance', 'user_experience', 'end_to_end_readiness', 'performance_readiness', 'user_acceptance', 'release_candidate'] as $stream) {
            self::assertStringContainsString($stream, $php);
        }
        foreach (['Infrastructure\\', 'PDO', 'PostgreSql', 'Runtime', 'Http', 'Event', 'Delivery', 'Outbox', 'Provider'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_repository_and_migration_materialize_only_persistence_guarantees(): void
    {
        $root = dirname(__DIR__, 2);
        $repository = (string) file_get_contents($root.'/src/Modules/ExperienceAcceptance/Infrastructure/Persistence/PostgreSql/PostgreSqlExperienceAcceptanceOwnerSource.php');
        foreach (['SAVEPOINT', 'pg_advisory_xact_lock', 'FOR UPDATE', 'ON CONFLICT', 'effective_at<=', 'recorded_at<='] as $guarantee) {
            self::assertStringContainsString($guarantee, $repository);
        }
        foreach (['Application\\Runtime', 'Http', 'Event', 'Delivery', 'Outbox', 'Provider'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $repository);
        }
        $migrations = glob($root.'/src/Modules/ExperienceAcceptance/Infrastructure/Persistence/PostgreSql/Migrations/*.sql') ?: [];
        self::assertCount(2, $migrations);
        self::assertStringContainsString('090_experience_acceptance_owner_source.sql', implode('|', $migrations));
        self::assertStringContainsString('090_experience_acceptance_owner_source.down.sql', implode('|', $migrations));
    }
}

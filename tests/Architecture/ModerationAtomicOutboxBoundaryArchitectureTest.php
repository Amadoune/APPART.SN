<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationAtomicOutboxBoundaryArchitectureTest extends TestCase
{
    #[Test]
    public function boundary_is_owner_local_and_framework_agnostic(): void
    {
        $root = dirname(__DIR__, 2);
        $application = implode("\n", array_map(
            static fn (string $file): string => (string) file_get_contents($file),
            glob($root.'/app/Application/ModerationAtomicOperation/*.php') ?: [],
        ));
        foreach (['PDO', 'PostgreSql', 'Illuminate', 'Laravel', 'Infrastructure'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $application);
        }

        $migration = (string) file_get_contents(
            $root.'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/066_moderation_atomic_outbox_appends.sql',
        );
        foreach (['FOREIGN KEY', 'CASCADE', 'TRIGGER'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, strtoupper($migration));
        }
        self::assertStringContainsString('moderation_reports.atomic_outbox_appends', $migration);
    }
}

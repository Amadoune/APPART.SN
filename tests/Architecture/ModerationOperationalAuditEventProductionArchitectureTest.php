<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationOperationalAuditEventProductionArchitectureTest extends TestCase
{
    #[Test]
    public function production_is_owner_local_without_routing_consumer_or_schema_changes(): void
    {
        $root = dirname(__DIR__, 2);
        $directory = $root.'/app/Application/ModerationOperationalAuditEventProduction';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            foreach ([
                'DeterministicModerationEventRouter', 'ModerationRoutingDestination',
                'AdministrationAudit', 'Controller', 'Route', 'Consumer',
                'RuntimeHealth', 'PDO', 'SELECT ', 'INSERT ',
            ] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source, $file->getPathname());
            }
        }

        $producer = (string) file_get_contents(
            $directory.'/OperationalAuditModerationCaseOrchestratorV1.php',
        );
        self::assertStringContainsString(
            '$result->status !== ModerationCommandStatus::Applied',
            $producer,
        );
        self::assertStringNotContainsString('AlreadyApplied]', $producer);
        self::assertFileDoesNotExist(
            $root.'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/072_moderation_operational_audit.sql',
        );
    }
}

<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationOperationalAuditArchitectureTest extends TestCase
{
    #[Test]
    public function integration_uses_only_public_audit_and_certified_moderation_boundaries(): void
    {
        $root = dirname(__DIR__, 2);
        $directory = $root.'/app/Application/ModerationOperationalAudit';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            foreach ([
                'PDO', 'SELECT ', 'INSERT ', 'UPDATE ', 'DELETE ',
                'PostgreSqlAdministrationAudit', 'Infrastructure\\Persistence',
                'Controller', 'Route', 'RuntimeHealth',
            ] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source, $file->getPathname());
            }
        }

        $consumer = (string) file_get_contents($directory.'/ModerationOperationalAuditConsumer.php');
        $factory = (string) file_get_contents($directory.'/ModerationOperationalAuditRecordFactory.php');
        self::assertStringContainsString('AdministrationAuditAppendV1', $consumer);
        self::assertStringContainsString('ModerationOutboxReaderV1', $consumer);
        self::assertStringContainsString('DeliveryObservation', $consumer);
        self::assertStringContainsString('ResidualOperationalAuditConversionMatrixV1', $factory);
        foreach (['ModerationDecisionStore', 'Repository', 'Snapshot', 'SQL'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $factory);
        }
    }
}

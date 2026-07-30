<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationOperationalAuditEventContractArchitectureTest extends TestCase
{
    #[Test]
    public function contracts_are_framework_infrastructure_transport_and_pii_free(): void
    {
        $root = dirname(__DIR__, 2);
        $directory = $root.'/src/Modules/ModerationReports/Application/OperationalAuditEventContract';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        $count = 0;

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $count++;
            $source = (string) file_get_contents($file->getPathname());
            foreach ([
                'Illuminate\\', 'PDO', 'PostgreSql', 'Runtime', 'Provider',
                'Controller', 'Route', 'Outbox', 'Delivery', 'Consumer',
                'Serializer', 'Repository', 'SQL', 'actorAccountId',
                'email', 'phone', 'evidence', 'document', 'diagnostic',
            ] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source, $file->getPathname());
            }
        }

        self::assertSame(6, $count);
    }

    #[Test]
    public function closed_catalog_and_documented_routing_matrix_are_exhaustive(): void
    {
        $root = dirname(__DIR__, 2);
        $catalog = (string) file_get_contents(
            $root.'/src/Modules/ModerationReports/Application/OperationalAuditEventContract/ModerationOperationalAuditEventTypeV1.php',
        );
        $routing = (string) file_get_contents(
            $root.'/docs/A-5.3-MODERATION-OPERATIONAL-AUDIT-EVENT-ROUTING-COVERAGE-01-ROUTING-MATRIX.md',
        );

        self::assertSame(2, substr_count($catalog, 'case '));
        foreach ([
            'moderation.report.submitted.v1',
            'moderation.report.validated.v1',
            'moderation.finding.recorded.v1',
            'moderation.queue-item.claimed.v1',
        ] as $eventType) {
            self::assertStringContainsString($eventType, $routing);
        }
        self::assertGreaterThanOrEqual(
            4,
            substr_count($routing, '`moderation.delivery-observation`'),
        );
    }
}

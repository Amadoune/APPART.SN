<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ModerationResidualOperationalAuditContractArchitectureTest extends TestCase
{
    #[Test]
    public function contracts_are_framework_agnostic_and_non_operational(): void
    {
        $directory = dirname(__DIR__, 2).'/app/Application/ModerationResidualOperationalAuditContract';
        $files = iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)));
        $php = array_filter($files, static fn (\SplFileInfo $file): bool => $file->isFile() && $file->getExtension() === 'php');

        self::assertCount(6, $php);
        foreach ($php as $file) {
            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            foreach ([
                'Illuminate\\',
                'PDO',
                'App\\Infrastructure\\',
                'Repository',
                'Snapshot',
                'Controller',
                'Provider',
                'Migration',
                'SELECT ',
                'INSERT ',
                'UPDATE ',
                'DELETE ',
            ] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getFilename());
            }
        }
    }

    #[Test]
    public function governance_documents_close_catalog_ownership_and_future_recertification(): void
    {
        $root = dirname(__DIR__, 2);
        $contracts = file_get_contents($root.'/docs/A-5.3-MODERATION-RESIDUAL-OPERATIONAL-AUDIT-COVERAGE-01-CONTRACTS-FOUNDATION.md');
        $ownership = file_get_contents($root.'/docs/A-5.3-MODERATION-RESIDUAL-OPERATIONAL-AUDIT-COVERAGE-01-OWNERSHIP-AND-TRANSACTIONS.md');
        $recertification = file_get_contents($root.'/docs/A-5.3-MODERATION-RESIDUAL-OPERATIONAL-AUDIT-COVERAGE-01-RECERTIFICATION-MATRIX.md');

        self::assertIsString($contracts);
        self::assertIsString($ownership);
        self::assertIsString($recertification);
        foreach ([
            'moderation.report.submitted.v1',
            'moderation.report.validated.v1',
            'moderation.finding.recorded.v1',
            'moderation.queue-item.claimed.v1',
            'moderation.decision.issued.v1',
            'moderation.case.closed.v1',
            'moderation.target-action.completed.v1',
        ] as $eventType) {
            self::assertStringContainsString($eventType, $contracts);
        }
        self::assertStringContainsString('byte-for-byte', $contracts);
        self::assertStringContainsString('transaction owner-locale unique', $ownership);
        self::assertStringContainsString('aucune transaction distribuée', $ownership);
        foreach (['5.3F', '5.3G', '5.3H', '5.3I'] as $foundation) {
            self::assertStringContainsString($foundation, $recertification);
        }
    }
}

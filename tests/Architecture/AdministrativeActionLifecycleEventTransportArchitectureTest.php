<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class AdministrativeActionLifecycleEventTransportArchitectureTest extends TestCase
{
    public function test_transport_foundation_has_no_execution_infrastructure_or_confidential_context(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/AdministrativeActionLifecycleEventTransport';
        $contents = '';

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile()) {
                $contents .= file_get_contents($file->getPathname());
            }
        }

        foreach ([
            'PostgreSql', 'PDO', 'Laravel', 'Inbox', 'Outbox', 'Consumer', 'Worker', 'Http',
            'HistoricalReason', 'DecisionContext', 'ApprovalId', 'DecisionId', 'FourEyes',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_transport_contract_remains_closed_after_later_runtime_composition(): void
    {
        self::assertSame(9, count(glob(dirname(__DIR__, 2).'/app/Application/AdministrativeActionLifecycleEventTransport/*.php') ?: []));
    }
}

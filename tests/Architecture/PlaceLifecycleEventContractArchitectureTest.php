<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PlaceLifecycleEventContractArchitectureTest extends TestCase
{
    public function test_event_contract_has_no_transport_runtime_or_sensitive_evidence(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Geography/Application/PlaceLifecycleEvent';
        $contents = '';

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile()) {
                $contents .= file_get_contents($file->getPathname());
            }
        }

        foreach ([
            'PDO',
            'PostgreSql',
            'Illuminate',
            'Laravel',
            'Transport',
            'Routing',
            'Outbox',
            'Publisher',
            'Consumer',
            'Worker',
            'Http',
            'PlaceRegistry',
            'PlaceMergeActorId',
            'PlaceMergeIntentId',
            'observedTargetVersion',
            'observedTargetState',
            'observedSourceType',
            'observedTargetType',
            'observedSourceCountry',
            'observedTargetCountry',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_event_contract_is_versioned_and_closed(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Geography/Application/PlaceLifecycleEvent';
        $types = (string) file_get_contents($root.'/PlaceLifecycleEventType.php');
        $versions = (string) file_get_contents($root.'/PlaceLifecycleEventPayloadVersion.php');

        self::assertSame(3, substr_count($types, 'case '));
        self::assertStringContainsString('case V1 = 1;', $versions);
        self::assertSame(1, substr_count($versions, 'case '));
    }
}

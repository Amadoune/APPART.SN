<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PlaceMergeContextArchitectureTest extends TestCase
{
    public function test_contract_is_application_only_and_has_no_technical_implementation(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Geography/Application/PlaceMergeContext';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $source = (string) file_get_contents($file->getPathname());
            foreach ([
                'Illuminate\\', 'PDO', 'PostgreSql', 'Repository', 'Migration',
                'Workflow', 'Aggregate', 'Outbox', 'Inbox', 'Event', 'Transport',
                'Routing', 'Consumer', 'Worker', 'Http', 'Transaction', 'now(',
                'random',
            ] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source, $file->getFilename());
            }
        }
    }

    public function test_only_contract_interfaces_exist_for_inspection_and_replay(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Geography/Application/PlaceMergeContext';

        self::assertFileExists($root.'/Contract/PlaceMergeContextInspector.php');
        self::assertFileExists($root.'/Contract/PlaceMergeReplayClassifier.php');
        self::assertSame([], glob($root.'/Infrastructure/*') ?: []);
        self::assertSame([], glob(dirname(__DIR__, 2).'/src/Modules/Geography/Infrastructure/*PlaceMerge*') ?: []);
    }

    public function test_existing_geography_domain_and_certified_runtime_are_not_amended(): void
    {
        $root = dirname(__DIR__, 2);
        $place = (string) file_get_contents($root.'/src/Modules/Geography/Domain/Model/Place.php');
        $registry = (string) file_get_contents($root.'/src/Modules/Geography/Application/Contract/PlaceRegistry.php');

        self::assertStringNotContainsString('PlaceMergeContext', $place);
        self::assertStringNotContainsString('PlaceMergeContext', $registry);
        self::assertSame([], glob($root.'/database/migrations/*place_merge*') ?: []);
        self::assertSame([], glob($root.'/database/migrations/postgresql/*place_merge*') ?: []);
    }
}

<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ModerationHttpReadBoundariesArchitectureTest extends TestCase
{
    public function test_contracts_are_framework_agnostic_and_the_four_readers_are_delivered_together(): void
    {
        $root = dirname(__DIR__, 2);
        $contracts = $root.'/src/Modules/ModerationReports/Application/HttpReadBoundaries';
        $readers = $root.'/app/Application/ModerationHttpReadBoundaries';

        foreach ([
            'ReadOwnModerationReportV1',
            'ReadModerationQueueV1',
            'ReadModerationCaseV1',
            'ReadModerationDecisionV1',
        ] as $name) {
            $contract = (string) file_get_contents($contracts.'/Contract/'.$name.'.php');
            self::assertStringNotContainsString('Illuminate\\', $contract);
            self::assertStringNotContainsString('PDO', $contract);
            self::assertStringNotContainsString('PostgreSql', $contract);
        }

        foreach (glob($readers.'/*.php') ?: [] as $path) {
            $source = (string) file_get_contents($path);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Repository', $source);
            self::assertStringNotContainsString('Http\\', $source);
        }
    }

    public function test_composition_is_unique_and_does_not_touch_http_or_runtime_health(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents(
            $root.'/app/Providers/ModerationHttpReadBoundariesServiceProvider.php',
        );
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');

        self::assertSame(1, substr_count($providers, 'ModerationHttpReadBoundariesServiceProvider::class'));
        self::assertStringNotContainsString('RuntimeHealth', $provider);
        self::assertStringNotContainsString('Route', $provider);
        self::assertStringNotContainsString('Controller', $provider);
        self::assertSame(4, substr_count($provider, '$this->bindReader('));
    }
}

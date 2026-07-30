<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class MultiTargetDeliveryStrategyArchitectureTest extends TestCase
{
    public function test_strategy_is_application_only_and_does_not_modify_certified_consumption(): void
    {
        $path = dirname(__DIR__, 2).'/app/Application/MultiTargetDelivery';
        foreach (glob($path.'/{,Contract/}*.php', GLOB_BRACE) ?: [] as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            foreach (['Illuminate', 'Laravel', '\\Infrastructure\\', 'PDO', 'PublicProjectionUpdaterConsumer', 'PublicProjectionSourceResolution'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
            self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents, $file);
        }
    }
}

<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ModerationHttpArchitectureTest extends TestCase
{
    public function test_controller_is_closed_and_has_no_persistence_dependency(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = (string) file_get_contents($root.'/app/Http/Controllers/ModerationHttpController.php');
        $runtime = (string) file_get_contents(
            $root.'/app/Application/ModerationHttp/DeterministicModerationHttpRuntimeV1.php',
        );

        foreach (['PDO', 'Repository', 'Aggregate', 'PostgreSql', 'ModerationCaseStore'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $controller);
            self::assertStringNotContainsString($forbidden, $runtime);
        }
        self::assertStringContainsString('ModerationHttpRuntimeV1', $controller);
        self::assertSame(10, substr_count((string) file_get_contents($root.'/routes/web.php'), 'moderation_operation'));
    }

    public function test_http_foundation_does_not_extend_runtime_health(): void
    {
        $provider = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Providers/ModerationHttpServiceProvider.php',
        );

        self::assertStringNotContainsString('RuntimeHealth', $provider);
        self::assertStringContainsString("RateLimiter::for('moderation-http-v1'", $provider);
        self::assertStringContainsString("hash_hmac('sha256'", $provider);
        self::assertStringContainsString("middleware([RequireIdentityAccessSession::class, 'throttle:moderation-http-v1'])", (string) file_get_contents(
            dirname(__DIR__, 2).'/routes/web.php',
        ));
    }
}

<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IdentityAccessHttpRuntimeArchitectureTest extends TestCase
{
    #[Test]
    public function http_adapter_does_not_modify_or_import_frozen_event_or_outbox_implementations(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = (string) file_get_contents($root.'/app/Http/Controllers/IdentityAccessHttpController.php');
        $request = (string) file_get_contents($root.'/app/Http/Requests/IdentityAccessHttpRequest.php');
        $middleware = (string) file_get_contents($root.'/app/Http/Middleware/RequireIdentityAccessSession.php');
        $source = $controller.$request.$middleware;

        foreach ([
            'IdentityAccessAtomicEventOrchestrator',
            'IdentityAccessEventV1',
            'IdentityAccessOutboxWriter',
            'PostgreSqlIdentityAccessOutbox',
            'PDO',
            'DB::',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    #[Test]
    public function routes_are_closed_self_scoped_and_have_no_account_identifier(): void
    {
        $routes = (string) file_get_contents(dirname(__DIR__, 2).'/routes/web.php');

        self::assertStringContainsString("prefix('/api/identity-access')", $routes);
        self::assertStringContainsString('RequireIdentityAccessSession::class', $routes);
        self::assertStringContainsString("'throttle:iam-login'", $routes);
        self::assertStringContainsString("'throttle:iam-recovery'", $routes);
        self::assertStringContainsString("'throttle:iam-authenticated'", $routes);
        self::assertStringNotContainsString('/identity-access/{accountId}', $routes);
    }

    #[Test]
    public function cookie_policy_is_secure_http_only_and_strict(): void
    {
        $controller = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Http/Controllers/IdentityAccessHttpController.php',
        );
        $config = require dirname(__DIR__, 2).'/config/identity_access_http.php';

        self::assertSame('__Host-appart_session', $config['cookie']['name']);
        self::assertSame('/', $config['cookie']['path']);
        self::assertNull($config['cookie']['domain']);
        self::assertSame('strict', $config['cookie']['same_site']);
        self::assertStringContainsString('true,', $controller);
    }
}

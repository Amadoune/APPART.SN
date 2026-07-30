<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IamModeratorAuthorizationImplementationArchitectureTest extends TestCase
{
    #[Test]
    public function implementation_and_binding_are_owner_scoped_and_unique(): void
    {
        $root = dirname(__DIR__, 2);
        $implementation = (string) file_get_contents(
            $root.'/src/Modules/IdentityAccess/Application/ModeratorAuthorization/OwnerModeratorAuthorizationReaderV1.php',
        );
        $provider = (string) file_get_contents(
            $root.'/app/Providers/ModeratorAuthorizationServiceProvider.php',
        );
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');

        foreach (['PDO', 'PostgreSql', 'Illuminate', 'Infrastructure', 'Snapshot'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $implementation);
        }
        self::assertStringNotContainsString('RuntimeHealth', $provider);
        self::assertSame(1, substr_count($provider, '$this->app->alias('));
        self::assertSame(1, substr_count($providers, 'ModeratorAuthorizationServiceProvider::class'));
        self::assertStringContainsString('singleton', $provider);
        self::assertStringContainsString('alias', $provider);
    }
}

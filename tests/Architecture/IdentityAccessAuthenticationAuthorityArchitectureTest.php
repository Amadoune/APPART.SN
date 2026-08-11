<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class IdentityAccessAuthenticationAuthorityArchitectureTest extends TestCase
{
    public function test_authorities_do_not_depend_on_http_laravel_or_other_modules(): void
    {
        $directory = dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Application/AuthenticationAuthority';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            self::assertStringNotContainsString('Illuminate\\', $source);
            self::assertStringNotContainsString('\\Http\\', $source);
            self::assertStringNotContainsString('Modules\\Media', $source);
            self::assertStringNotContainsString('Modules\\ListingLifecycle', $source);
        }
    }

    public function test_http_runtime_binding_uses_the_certified_f2_composition(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/IdentityAccessHttpServiceProvider.php');
        self::assertStringContainsString('DeterministicIdentityAccessHttpRuntime::class', $provider);
        self::assertStringNotContainsString('FailClosedIdentityAccessHttpRuntime::class', $provider);
    }
}

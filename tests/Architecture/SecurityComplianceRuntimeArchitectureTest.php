<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class SecurityComplianceRuntimeArchitectureTest extends TestCase
{
    public function test_application_runtime_depends_only_on_the_owner_port(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SecurityCompliance/Application/Runtime';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = (string) file_get_contents($file->getPathname());
            foreach (['PDO', 'Illuminate\\', 'Infrastructure\\', 'PostgreSql', 'Mapper', 'RuntimeRead', 'OwnerReader', 'ReaderV1', 'App\\Http', 'Event\\', 'Delivery\\', 'Outbox\\', 'Consumer', 'Transport', 'Routing', 'SQL'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }
    }

    public function test_provider_bindings_and_registration_are_unique(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/SecurityComplianceRuntimeServiceProvider.php');
        self::assertSame(1, preg_match_all('/->alias\(PostgreSqlSecurityComplianceOwnerSource::class, SecurityComplianceOwnerSource::class\)/', $provider));
        self::assertSame(1, preg_match_all('/->alias\(DeterministicSecurityComplianceRuntimeAvailabilityPolicy::class, SecurityComplianceRuntimeAvailabilityPolicy::class\)/', $provider));
        self::assertSame(1, preg_match_all('/->alias\(DeterministicSecurityComplianceRuntime::class, SecurityComplianceRuntimeV1::class\)/', $provider));
        self::assertStringContainsString('singleton', $provider);
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        self::assertSame(2, substr_count($providers, 'SecurityComplianceRuntimeServiceProvider'));
    }

    public function test_migration_086_is_frozen_by_sha256(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SecurityCompliance/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('b0e5e97d49f4b9f9f901db9e4ff61d6c12e50ba67e66c300bb9a0e610803ee93', hash_file('sha256', $root.'086_security_compliance_owner_source.sql'));
        self::assertSame('113b3965a12d9c7927cc596fb6dfc1607e85f11e71ac50f0c546e30eeac97e57', hash_file('sha256', $root.'086_security_compliance_owner_source.down.sql'));
    }
}

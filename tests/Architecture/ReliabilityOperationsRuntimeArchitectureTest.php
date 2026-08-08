<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ReliabilityOperationsRuntimeArchitectureTest extends TestCase
{
    public function test_application_runtime_depends_only_on_the_owner_port(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ReliabilityOperations/Application/Runtime';
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
        $provider = (string) file_get_contents($root.'/app/Providers/ReliabilityOperationsRuntimeServiceProvider.php');
        self::assertSame(1, preg_match_all('/->alias\(PostgreSqlReliabilityOperationsOwnerSource::class, ReliabilityOperationsOwnerSource::class\)/', $provider));
        self::assertSame(1, preg_match_all('/->alias\(DeterministicReliabilityOperationsRuntimeAvailabilityPolicy::class, ReliabilityOperationsRuntimeAvailabilityPolicy::class\)/', $provider));
        self::assertSame(1, preg_match_all('/->alias\(DeterministicReliabilityOperationsRuntime::class, ReliabilityOperationsRuntimeV1::class\)/', $provider));
        self::assertStringContainsString('singleton', $provider);
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        self::assertSame(2, substr_count($providers, 'ReliabilityOperationsRuntimeServiceProvider'));
    }

    public function test_migration_088_is_frozen_by_sha256(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ReliabilityOperations/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('1f0db96df9513b40b498f7daf9cd3c4607142f12b1b00bafffed7040e22aec3e', hash_file('sha256', $root.'088_reliability_operations_owner_source.sql'));
        self::assertSame('f25a5bf041f7a823c5856c17940a53ad956da0931ce1fd918a492e3236139bb2', hash_file('sha256', $root.'088_reliability_operations_owner_source.down.sql'));
    }
}

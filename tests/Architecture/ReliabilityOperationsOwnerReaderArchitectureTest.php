<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ReliabilityOperationsOwnerReaderArchitectureTest extends TestCase
{
    public function test_exactly_seven_owner_readers_depend_only_on_the_owner_source(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ReliabilityOperations/Application/OwnerReader';
        $files = glob($root.'/*.php') ?: [];
        self::assertCount(7, $files);
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), $files));
        foreach (['Observability', 'ServiceHealth', 'Alerting', 'MaintenanceOperations', 'Continuity', 'CapacityPlanning', 'OperationalReadiness'] as $reader) {
            self::assertStringContainsString('final readonly class '.$reader.'OwnerReader', $php);
            self::assertStringContainsString('implements '.$reader.'ReaderV1', $php);
        }
        self::assertSame(7, substr_count($php, 'private ReliabilityOperationsOwnerSource $source'));
        self::assertSame(28, substr_count($php, 'ReliabilityOperationsReadStatus::'));
        foreach (['Application\\Runtime', 'RuntimeV1', 'PDO', 'PostgreSql', 'Mapper', 'Infrastructure\\', 'Http', 'Event\\', 'Delivery\\', 'Outbox\\', 'Transport', 'Routing', 'Consumer', 'default'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_provider_has_exactly_seven_singleton_aliases_and_one_registration(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/ReliabilityOperationsOwnerReaderServiceProvider.php');
        self::assertSame(7, substr_count($provider, '->singleton('));
        self::assertSame(7, substr_count($provider, '->alias('));
        foreach (['Observability', 'ServiceHealth', 'Alerting', 'MaintenanceOperations', 'Continuity', 'CapacityPlanning', 'OperationalReadiness'] as $reader) {
            self::assertSame(1, preg_match_all('/->alias\('.$reader.'OwnerReader::class, '.$reader.'ReaderV1::class\)/', $provider));
        }
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        self::assertSame(2, substr_count($providers, 'ReliabilityOperationsOwnerReaderServiceProvider'));
    }

    public function test_migration_088_remains_frozen(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ReliabilityOperations/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('1f0db96df9513b40b498f7daf9cd3c4607142f12b1b00bafffed7040e22aec3e', hash_file('sha256', $root.'088_reliability_operations_owner_source.sql'));
        self::assertSame('f25a5bf041f7a823c5856c17940a53ad956da0931ce1fd918a492e3236139bb2', hash_file('sha256', $root.'088_reliability_operations_owner_source.down.sql'));
    }
}

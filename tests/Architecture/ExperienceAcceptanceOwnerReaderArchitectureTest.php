<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ExperienceAcceptanceOwnerReaderArchitectureTest extends TestCase
{
    public function test_exactly_seven_owner_readers_depend_only_on_the_owner_source(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ExperienceAcceptance/Application/OwnerReader';
        $files = glob($root.'/*.php') ?: [];
        self::assertCount(7, $files);
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), $files));
        foreach (['ResponsiveCompliance', 'AccessibilityCompliance', 'UserExperience', 'EndToEndReadiness', 'PerformanceReadiness', 'UserAcceptance', 'ReleaseCandidate'] as $reader) {
            self::assertStringContainsString('final readonly class '.$reader.'OwnerReader', $php);
            self::assertStringContainsString('implements '.$reader.'ReaderV1', $php);
        }
        self::assertSame(7, substr_count($php, 'private ExperienceAcceptanceOwnerSource $source'));
        self::assertSame(28, substr_count($php, 'ExperienceAcceptanceReadStatus::'));
        foreach (['Application\\Runtime', 'RuntimeV1', 'PDO', 'PostgreSql', 'Mapper', 'Repository', 'Infrastructure\\', 'Http', 'Event\\', 'Delivery\\', 'Outbox\\', 'Transport', 'Routing', 'Consumer', 'default', 'score', 'metric', 'aggregate'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_provider_has_exactly_seven_singleton_aliases_and_one_registration(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/ExperienceAcceptanceOwnerReaderServiceProvider.php');
        self::assertSame(7, substr_count($provider, '->singleton('));
        self::assertSame(7, substr_count($provider, '->alias('));
        foreach (['ResponsiveCompliance', 'AccessibilityCompliance', 'UserExperience', 'EndToEndReadiness', 'PerformanceReadiness', 'UserAcceptance', 'ReleaseCandidate'] as $reader) {
            self::assertSame(1, preg_match_all('/->alias\\('.$reader.'OwnerReader::class, '.$reader.'ReaderV1::class\\)/', $provider));
        }
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        self::assertSame(2, substr_count($providers, 'ExperienceAcceptanceOwnerReaderServiceProvider'));
    }

    public function test_migration_090_remains_frozen(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ExperienceAcceptance/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('0c49f30b31338537045a2e603c7354844e871efd45c18a32b3b2fb11e7776059', hash_file('sha256', $root.'090_experience_acceptance_owner_source.sql'));
        self::assertSame('3fd63032924715010ec6a703a81ebf8ed31c38f9ce8730a7a1937af03600d79a', hash_file('sha256', $root.'090_experience_acceptance_owner_source.down.sql'));
    }
}

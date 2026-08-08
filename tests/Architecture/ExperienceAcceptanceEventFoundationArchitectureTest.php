<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ExperienceAcceptanceEventFoundationArchitectureTest extends TestCase
{
    public function test_exactly_seven_event_catalogues_depend_only_on_public_readers(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ExperienceAcceptance/Application/Event';
        $files = [];
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
                $php .= (string) file_get_contents($file->getPathname());
            }
        }
        self::assertCount(35, $files);
        foreach (['ResponsiveCompliance', 'AccessibilityCompliance', 'UserExperience', 'EndToEndReadiness', 'PerformanceReadiness', 'UserAcceptance', 'ReleaseCandidate'] as $stream) {
            self::assertStringContainsString($stream.'ReaderV1', $php);
            self::assertSame(1, substr_count($php, 'final readonly class '.$stream.'EventFactory'));
        }
        self::assertSame(7, substr_count($php, "return ['status' => \$this->status->value, 'observedAt' => \$this->observedAt];"));
        foreach (['OwnerSource', 'Application\\Runtime', 'RuntimeRead', 'App\\Http', 'PostgreSql', 'Mapper', 'Repository', 'PDO', 'Infrastructure\\', 'Provider', 'Delivery', 'Outbox', 'default'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_types_are_exact_and_migration_090_is_unchanged(): void
    {
        $root = dirname(__DIR__, 2);
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), glob($root.'/src/Modules/ExperienceAcceptance/Application/Event/*EventType.php') ?: []));
        foreach (['responsive-compliance', 'accessibility-compliance', 'user-experience', 'end-to-end-readiness', 'performance-readiness', 'user-acceptance', 'release-candidate'] as $stream) {
            self::assertSame(1, substr_count($php, 'experience-acceptance.'.$stream.'.observed.v1'));
        }
        $migrations = $root.'/src/Modules/ExperienceAcceptance/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('0c49f30b31338537045a2e603c7354844e871efd45c18a32b3b2fb11e7776059', hash_file('sha256', $migrations.'090_experience_acceptance_owner_source.sql'));
        self::assertSame('3fd63032924715010ec6a703a81ebf8ed31c38f9ce8730a7a1937af03600d79a', hash_file('sha256', $migrations.'090_experience_acceptance_owner_source.down.sql'));
    }
}

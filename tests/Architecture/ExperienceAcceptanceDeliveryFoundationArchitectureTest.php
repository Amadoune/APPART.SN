<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ExperienceAcceptanceDeliveryFoundationArchitectureTest extends TestCase
{
    public function test_delivery_depends_only_on_seven_events_v1(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ExperienceAcceptance/Application/Delivery';
        $files = [];
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
                $php .= (string) file_get_contents($file->getPathname());
            }
        }
        self::assertCount(35, $files);
        foreach (['ResponsiveCompliance', 'AccessibilityCompliance', 'UserExperience', 'EndToEndReadiness', 'PerformanceReadiness', 'UserAcceptance', 'ReleaseCandidate'] as $family) {
            self::assertStringContainsString($family.'EventV1', $php);
            self::assertSame(1, substr_count($php, 'final readonly class '.$family.'DeliveryFactory'));
        }
        self::assertSame(7, substr_count($php, 'public function create('));
        self::assertSame(7, substr_count($php, "return ['status' => \$this->status->value, 'observedAt' => \$this->observedAt];"));
        foreach (['OwnerSource', 'Reader', 'Application\\Runtime', 'RuntimeRead', 'App\\Http', 'PostgreSql', 'Repository', 'Mapper', 'PDO', 'Infrastructure\\', 'Provider', 'Transport', 'Routing', 'Consumer', 'Outbox', 'default'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_migration_090_and_rollback_remain_unchanged(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ExperienceAcceptance/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('0c49f30b31338537045a2e603c7354844e871efd45c18a32b3b2fb11e7776059', hash_file('sha256', $root.'090_experience_acceptance_owner_source.sql'));
        self::assertSame('3fd63032924715010ec6a703a81ebf8ed31c38f9ce8730a7a1937af03600d79a', hash_file('sha256', $root.'090_experience_acceptance_owner_source.down.sql'));
    }
}

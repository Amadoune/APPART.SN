<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ExperienceAcceptanceHttpFoundationArchitectureTest extends TestCase
{
    public function test_http_surface_depends_only_on_seven_public_readers(): void
    {
        $root = dirname(__DIR__, 2);
        $files = array_merge(glob($root.'/app/Http/Controllers/ExperienceAcceptance*Controller.php') ?: [], glob($root.'/app/Http/Requests/ExperienceAcceptance*Request.php') ?: [], glob($root.'/app/Http/ExperienceAcceptance/*.php') ?: []);
        self::assertCount(16, $files);
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), $files));
        foreach (['ResponsiveComplianceReaderV1', 'AccessibilityComplianceReaderV1', 'UserExperienceReaderV1', 'EndToEndReadinessReaderV1', 'PerformanceReadinessReaderV1', 'UserAcceptanceReaderV1', 'ReleaseCandidateReaderV1'] as $reader) {
            self::assertStringContainsString($reader, $php);
        }
        foreach (['ExperienceAcceptanceOwnerSource', 'Application\\Runtime', 'PostgreSql', 'Mapper', 'Repository', 'PDO', 'Infrastructure\\', 'Event\\', 'Delivery\\', 'Outbox\\'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_provider_routes_and_registration_are_unique(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/ExperienceAcceptanceHttpServiceProvider.php');
        self::assertSame(8, substr_count($provider, '->singleton('));
        foreach (['responsive-compliance', 'accessibility-compliance', 'user-experience', 'end-to-end-readiness', 'performance-readiness', 'user-acceptance', 'release-candidate'] as $route) {
            self::assertSame(1, substr_count($provider, "name('experience-acceptance.".$route."')"));
        }
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        self::assertSame(2, substr_count($providers, 'ExperienceAcceptanceHttpServiceProvider'));
    }

    public function test_migration_090_remains_frozen(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ExperienceAcceptance/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('0c49f30b31338537045a2e603c7354844e871efd45c18a32b3b2fb11e7776059', hash_file('sha256', $root.'090_experience_acceptance_owner_source.sql'));
        self::assertSame('3fd63032924715010ec6a703a81ebf8ed31c38f9ce8730a7a1937af03600d79a', hash_file('sha256', $root.'090_experience_acceptance_owner_source.down.sql'));
    }
}

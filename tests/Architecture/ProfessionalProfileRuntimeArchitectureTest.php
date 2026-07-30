<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProfessionalProfileRuntimeArchitectureTest extends TestCase
{
    #[Test]
    public function runtime_application_is_framework_infrastructure_and_status_free(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/ProfessionalProfileRuntime';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            foreach (['\\Infrastructure\\', 'Illuminate\\', 'PDO', 'SELECT ', 'ProfessionalStatus', 'Controller', 'Route', 'Event', 'Outbox', 'Delivery'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source);
            }
        }
    }

    #[Test]
    public function providers_compose_only_the_three_certified_persistences(): void
    {
        $root = dirname(__DIR__, 2);
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        foreach (['ProfessionalPublicProfile', 'ProfessionalVerification', 'ProfessionalPublicPortfolio', 'ProfessionalProfile'] as $owner) {
            $provider = $owner.'RuntimeServiceProvider';
            self::assertStringContainsString($provider.'::class', $providers);
            $source = (string) file_get_contents($root.'/app/Providers/'.$provider.'.php');
            foreach (['ProfessionalStatus', 'Controller', 'Route', 'Middleware', 'Event', 'Delivery', 'Outbox', 'SELECT '] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source);
            }
        }
    }

    #[Test]
    public function runtime_health_catalogue_includes_the_two_certified_owner_reads(): void
    {
        $requirements = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        self::assertSame(60, substr_count($requirements, 'new RuntimeHealthRequirement('));
        self::assertStringNotContainsString('ProfessionalProfile', $requirements);
        self::assertStringContainsString('RuntimeHealthComponent::ProfessionalMandateResolver, ProfessionalMandateResolverV1::class', $requirements);
        self::assertStringContainsString('RuntimeHealthComponent::ProfessionalPublicStatusReader, ProfessionalPublicStatusReaderV1::class', $requirements);
    }
}

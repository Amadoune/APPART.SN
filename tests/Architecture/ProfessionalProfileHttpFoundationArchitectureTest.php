<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProfessionalProfileHttpFoundationArchitectureTest extends TestCase
{
    #[Test]
    public function controller_is_a_closed_adapter_without_domain_or_persistence_decision(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = (string) file_get_contents($root.'/app/Http/Controllers/ProfessionalProfileHttpController.php');

        foreach (['ProfessionalStatusState', 'ProfessionalMandateResolutionStatusV1', 'ProfessionalRegistry', 'RepresentativeMandate', 'PDO', 'SELECT ', 'Infrastructure\\', 'Event', 'Delivery', 'Outbox'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $controller);
        }
        self::assertStringNotContainsString('match (', $controller);
        self::assertStringNotContainsString('if (', $controller);
    }

    #[Test]
    public function routes_provider_and_runtime_health_are_unique_and_additive(): void
    {
        $root = dirname(__DIR__, 2);
        $routes = (string) file_get_contents($root.'/routes/web.php');
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        $requirements = (string) file_get_contents($root.'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        $extension = (string) file_get_contents($root.'/app/Application/ProfessionalEndpoint/ProfessionalEndpointRuntimeHealthInspector.php');

        self::assertSame(1, substr_count($routes, "'/professional/mandate'"));
        self::assertSame(1, substr_count($routes, "'/professional/status'"));
        self::assertSame(1, substr_count($providers, 'ProfessionalProfileHttpServiceProvider::class'));
        self::assertSame(60, substr_count($requirements, 'new RuntimeHealthRequirement('));
        self::assertSame(2, substr_count($extension, 'RuntimeHealthComponent::ProfessionalEndpoint'));
        self::assertStringContainsString('ProfessionalEndpointRuntimeV1::class', $extension);
    }

    #[Test]
    public function http_foundation_adds_no_sql_migration_event_delivery_or_outbox(): void
    {
        $root = dirname(__DIR__, 2);
        $files = [
            $root.'/app/Application/ProfessionalEndpoint',
            $root.'/app/Http/Controllers/ProfessionalProfileHttpController.php',
            $root.'/app/Http/ProfessionalMandateHttpPresenter.php',
            $root.'/app/Http/ProfessionalStatusHttpPresenter.php',
            $root.'/app/Providers/ProfessionalProfileHttpServiceProvider.php',
        ];

        foreach ($files as $path) {
            $iterator = is_dir($path)
                ? new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path))
                : [new \SplFileInfo($path)];
            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                $source = (string) file_get_contents($file->getPathname());
                foreach (['PDO', 'SELECT ', 'INSERT ', 'UPDATE ', 'Migration', 'Event', 'Delivery', 'Outbox'] as $forbidden) {
                    self::assertStringNotContainsString($forbidden, $source);
                }
            }
        }
    }
}

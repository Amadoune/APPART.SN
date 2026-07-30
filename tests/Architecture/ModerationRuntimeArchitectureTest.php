<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationRuntimeArchitectureTest extends TestCase
{
    #[Test]
    public function runtime_application_is_framework_infrastructure_and_external_domain_free(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/ModerationRuntime';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            foreach ([
                '\\Infrastructure\\',
                'Illuminate\\',
                'PDO',
                'SELECT ',
                'Controller',
                'Route',
                'Middleware',
                'Event',
                'Delivery',
                'Outbox',
                'IdentityAccess',
                'ListingLifecycle',
                'Modules\\Media',
                'Modules\\Professionals',
                'AdministrationAudit',
            ] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source, $file->getPathname());
            }
        }
    }

    #[Test]
    public function provider_is_unique_owner_scoped_and_additive(): void
    {
        $root = dirname(__DIR__, 2);
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        $provider = (string) file_get_contents($root.'/app/Providers/ModerationRuntimeServiceProvider.php');

        self::assertSame(1, substr_count($providers, 'ModerationRuntimeServiceProvider::class'));
        foreach (['ModerationCaseStore', 'ModerationDecisionStore', 'ModerationQueueStore', 'ModerationRuntimeV1', 'ModerationQueueRuntimeV1'] as $contract) {
            self::assertStringContainsString($contract.'::class', $provider);
        }
        foreach (['Controller', 'Route', 'Middleware', 'Event', 'Delivery', 'Outbox', 'IdentityAccess', 'ListingLifecycle', 'AdministrationAudit'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    #[Test]
    public function health_extension_adds_exactly_two_components_without_changing_historical_requirements(): void
    {
        $root = dirname(__DIR__, 2);
        $requirements = (string) file_get_contents($root.'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        $components = (string) file_get_contents($root.'/app/Application/RuntimeHealth/RuntimeHealthComponent.php');
        $extension = (string) file_get_contents($root.'/app/Application/ModerationRuntime/ModerationRuntimeHealthInspector.php');

        self::assertSame(60, substr_count($requirements, 'new RuntimeHealthRequirement('));
        self::assertSame(1, substr_count($components, "case ModerationRuntime = 'moderation_runtime'"));
        self::assertSame(1, substr_count($components, "case ModerationQueue = 'moderation_queue'"));
        self::assertSame(2, substr_count($extension, 'RuntimeHealthComponent::ModerationRuntime'));
        self::assertSame(2, substr_count($extension, 'RuntimeHealthComponent::ModerationQueue'));
    }

    #[Test]
    public function sprint_adds_no_http_event_delivery_outbox_or_migration(): void
    {
        $root = dirname(__DIR__, 2);

        self::assertFileDoesNotExist($root.'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/064_moderation_runtime.sql');
        self::assertDirectoryDoesNotExist($root.'/app/Http/Controllers/ModerationReports');
        self::assertDirectoryDoesNotExist($root.'/app/Application/ModerationEvent');
        self::assertDirectoryDoesNotExist($root.'/app/Infrastructure/ModerationOutbox');
    }
}

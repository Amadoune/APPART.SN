<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationReportOwnerReadSourceArchitectureTest extends TestCase
{
    #[Test]
    public function application_contract_is_framework_sql_and_http_free(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ModerationReports/Application/ReportOwnerReadSource';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            foreach ([
                '\\Infrastructure\\', 'Illuminate\\', 'PDO', 'SELECT ', 'Repository',
                'Controller', 'Route', 'Middleware', 'Request', 'Resource',
                'Event', 'Delivery', 'Outbox',
            ] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source, $file->getPathname());
            }
        }
    }

    #[Test]
    public function binding_is_unique_lazy_and_does_not_change_runtime_health_or_routes(): void
    {
        $root = dirname(__DIR__, 2);
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        $provider = (string) file_get_contents($root.'/app/Providers/ModerationReportOwnerReadSourceServiceProvider.php');
        $routes = (string) file_get_contents($root.'/routes/web.php');

        self::assertSame(1, substr_count($providers, 'ModerationReportOwnerReadSourceServiceProvider::class'));
        self::assertSame(
            1,
            substr_count(
                $provider,
                "PostgreSqlModerationReportOwnerReadSourceV1::class,\n            ModerationReportOwnerReadSourceV1::class,",
            ),
        );
        self::assertStringContainsString('singleton(PostgreSqlModerationReportOwnerReadSourceV1::class)', $provider);
        self::assertStringNotContainsString('RuntimeHealth', $provider);
        self::assertStringNotContainsString('moderation/reports', $routes);
    }

    #[Test]
    public function foundation_adds_no_migration_query_or_http_component(): void
    {
        $root = dirname(__DIR__, 2);
        $source = '';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(
            $root.'/src/Modules/ModerationReports/Application/ReportOwnerReadSource',
        ));
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $source .= (string) file_get_contents($file->getPathname());
            }
        }
        $source .= (string) file_get_contents(
            $root.'/app/Providers/ModerationReportOwnerReadSourceServiceProvider.php',
        );

        self::assertFileDoesNotExist($root.'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/070_moderation_report_owner_read_source.sql');
        self::assertDirectoryDoesNotExist($root.'/app/Http/Controllers/ModerationReports');
        foreach (['ModerationHttp', 'Controller', 'Route', 'Request', 'Resource'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
        self::assertDirectoryDoesNotExist($root.'/app/Application/ModerationQueries');
    }
}

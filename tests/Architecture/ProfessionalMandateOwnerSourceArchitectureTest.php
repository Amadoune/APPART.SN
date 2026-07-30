<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProfessionalMandateOwnerSourceArchitectureTest extends TestCase
{
    #[Test]
    public function application_source_exposes_no_owner_internals_or_framework(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Professionals/Application/ProfessionalMandateOwnerSource';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            foreach ([
                'ProfessionalRegistry',
                'RepresentativeId',
                'RepresentativeMandate',
                'MandateId',
                'Establishment',
                '\\Infrastructure\\',
                'Illuminate\\',
                'PDO',
                'SELECT ',
                'Repository',
                'Runtime',
                'Http',
            ] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source);
            }
        }
    }

    #[Test]
    public function migration_is_additive_owner_scoped_and_has_no_cross_domain_constraint(): void
    {
        $root = dirname(__DIR__, 2);
        $up = (string) file_get_contents($root.'/src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/Migrations/062_professional_mandate_owner_source.sql');
        $down = (string) file_get_contents($root.'/src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/Migrations/062_professional_mandate_owner_source.down.sql');

        self::assertStringContainsString('CREATE SCHEMA IF NOT EXISTS professional_core', $up);
        self::assertStringContainsString('mandate_owner_sources', $up);
        self::assertStringNotContainsString('FOREIGN KEY', strtoupper($up));
        self::assertStringNotContainsString('CASCADE', strtoupper($up));
        self::assertStringContainsString('DROP SCHEMA IF EXISTS professional_core', $down);
    }

    #[Test]
    public function sprint_creates_no_resolver_binding_provider_runtime_or_http(): void
    {
        $root = dirname(__DIR__, 2);
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        $routes = (string) file_get_contents($root.'/routes/web.php');

        self::assertStringNotContainsString('ProfessionalMandateOwnerSource', $providers);
        self::assertStringNotContainsString('ProfessionalMandateResolverV1', $providers);
        self::assertStringNotContainsString('professional-mandate-owner-source', $routes);
    }
}

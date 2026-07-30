<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProfessionalMandatePublicResolutionBoundaryArchitectureTest extends TestCase
{
    #[Test]
    public function public_contract_exposes_no_aggregate_mandate_or_cross_module_type(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Professionals/Application/ProfessionalMandateResolution';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            foreach ([
                'Modules\\IdentityAccess\\',
                'ProfessionalRegistry',
                'RepresentativeMandate',
                'RepresentativeId',
                'Establishment',
                '\\Infrastructure\\',
                'Illuminate\\',
                'PDO',
                'SELECT ',
                'history',
            ] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source);
            }
        }
    }

    #[Test]
    public function amendment_creates_no_adapter_runtime_http_event_or_binding(): void
    {
        $root = dirname(__DIR__, 2);
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        $routes = (string) file_get_contents($root.'/routes/web.php');

        self::assertStringNotContainsString('ProfessionalMandateResolverV1', $providers);
        self::assertStringNotContainsString('professional-mandate-resolution', $routes);
        self::assertSame([], glob($root.'/app/**/*ProfessionalMandateResolution*', GLOB_BRACE) ?: []);
    }
}

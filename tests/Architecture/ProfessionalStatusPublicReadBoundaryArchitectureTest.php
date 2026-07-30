<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProfessionalStatusPublicReadBoundaryArchitectureTest extends TestCase
{
    #[Test]
    public function public_read_contract_exposes_no_owner_internals(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Professionals/Application/ProfessionalStatusPublicRead';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            foreach ([
                'ProfessionalStatusWorkflowStore',
                'ProfessionalStatusStoredState',
                'ProfessionalStatusPersistenceReadResult',
                'ProfessionalStatusState',
                'ProfessionalStatusOrchestrator',
                '\\Infrastructure\\',
                'Illuminate\\',
                'PDO',
                'SELECT ',
                'version',
                'history',
            ] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source);
            }
        }
    }

    #[Test]
    public function amendment_creates_no_runtime_http_event_or_delivery_artifact(): void
    {
        $root = dirname(__DIR__, 2);
        foreach ([
            'app/Providers/*ProfessionalPublicStatus*',
            'app/Http/**/*ProfessionalPublicStatus*',
            'app/Application/**/*ProfessionalPublicStatus*',
        ] as $pattern) {
            self::assertSame([], glob($root.'/'.$pattern, GLOB_BRACE) ?: []);
        }

        $routes = (string) file_get_contents($root.'/routes/web.php');
        self::assertStringNotContainsString('professional-public-status', $routes);
    }
}

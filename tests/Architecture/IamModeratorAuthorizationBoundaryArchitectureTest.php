<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IamModeratorAuthorizationBoundaryArchitectureTest extends TestCase
{
    #[Test]
    public function boundary_is_application_owned_read_only_and_infrastructure_free(): void
    {
        $root = dirname(__DIR__, 2);
        $directory = $root.'/src/Modules/IdentityAccess/Application/ModeratorAuthorization';
        $sources = '';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $sources .= (string) file_get_contents($file->getPathname());
            }
        }

        foreach ([
            'PDO',
            'PostgreSql',
            'Illuminate',
            'Laravel',
            'Infrastructure',
            'Repository',
            'RoleAssignment',
            'AccountAvailabilityInspector',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $sources);
        }
        self::assertStringContainsString('interface ModeratorAuthorizationReaderV1', $sources);
        self::assertSame(5, substr_count($sources, 'case Report')
            + substr_count($sources, 'case Validate')
            + substr_count($sources, 'case Investigate')
            + substr_count($sources, 'case Decide')
            + substr_count($sources, 'case Audit'));
        self::assertSame(4, substr_count($sources, 'case Allowed')
            + substr_count($sources, 'case Denied')
            + substr_count($sources, 'case Corrupted')
            + substr_count($sources, 'case DependencyUnavailable'));
    }
}

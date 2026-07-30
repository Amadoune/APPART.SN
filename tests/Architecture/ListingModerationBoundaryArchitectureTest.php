<?php

namespace Tests\Architecture;

use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationCommandResultV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationEligibilityV1;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ListingModerationBoundaryArchitectureTest extends TestCase
{
    #[Test]
    public function boundary_is_public_owner_local_and_infrastructure_free(): void
    {
        $directory = dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Application/ModerationBoundary';
        $sources = '';
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
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
            'Projection',
            'RuntimeHealth',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $sources);
        }
        self::assertStringContainsString('interface ListingModerationReaderV1', $sources);
        self::assertStringContainsString('interface ListingModerationCommandGatewayV1', $sources);
        self::assertSame(5, count(ListingModerationEligibilityV1::cases()));
        self::assertSame(6, count(ListingModerationCommandResultV1::cases()));
    }
}

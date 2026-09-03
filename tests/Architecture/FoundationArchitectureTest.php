<?php

namespace Tests\Architecture;

use FilesystemIterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class FoundationArchitectureTest extends TestCase
{
    private const MODULES = [
        'AdministrationAudit',
        'AdministrationConsole',
        'ContactsLeads',
        'ContentSeo',
        'ExperienceAcceptance',
        'Geography',
        'IdentityAccess',
        'LegacyMigration',
        'ListingLifecycle',
        'Media',
        'ModerationReports',
        'MonetizationPayments',
        'Notifications',
        'Professionals',
        'PublicationReview',
        'RealEstateCatalog',
        'ReliabilityOperations',
        'ReservationLifecycle',
        'SearchDiscovery',
        'SecurityCompliance',
    ];

    public function test_the_module_envelopes_exist(): void
    {
        $modulesPath = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Modules';
        $directories = [];

        foreach (new FilesystemIterator($modulesPath, FilesystemIterator::SKIP_DOTS) as $item) {
            if ($item->isDir()) {
                $directories[] = $item->getFilename();
            }
        }

        sort($directories);

        self::assertSame(self::MODULES, $directories);
    }

    #[DataProvider('foundationDirectoryProvider')]
    public function test_the_validated_foundation_directories_exist(string $relativePath): void
    {
        self::assertDirectoryExists(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.$relativePath);
    }

    public function test_the_domain_has_no_framework_dependency(): void
    {
        $srcPath = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'src';
        $phpFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcPath));

        foreach ($phpFiles as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $normalizedPath = str_replace('\\', '/', $file->getPathname());
            if (! str_contains($normalizedPath, '/Domain/')) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            self::assertIsString($contents);
            self::assertStringNotContainsString('Illuminate\\', $contents, $file->getPathname());
            self::assertStringNotContainsString('Laravel\\', $contents, $file->getPathname());
            self::assertStringNotContainsString('App\\', $contents, $file->getPathname());
        }

        self::addToAssertionCount(1);
    }

    public static function foundationDirectoryProvider(): array
    {
        return [
            ['src'],
            ['src'.DIRECTORY_SEPARATOR.'Modules'],
            ['src'.DIRECTORY_SEPARATOR.'Shared'],
            ['app'.DIRECTORY_SEPARATOR.'Interfaces'],
            ['app'.DIRECTORY_SEPARATOR.'Adapters'],
            ['app'.DIRECTORY_SEPARATOR.'Projections'],
            ['tests'],
            ['docs'],
            ['tools'.DIRECTORY_SEPARATOR.'temporary'],
        ];
    }
}

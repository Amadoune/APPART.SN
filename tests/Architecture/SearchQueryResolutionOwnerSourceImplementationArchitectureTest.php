<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class SearchQueryResolutionOwnerSourceImplementationArchitectureTest extends TestCase
{
    public function test_boundary_is_application_only_and_has_one_owner_source_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Application/SearchQueryResolutionOwnerReader';
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $php .= (string) file_get_contents($file->getPathname());
            }
        }

        self::assertStringContainsString('SearchQueryResolutionOwnerSource', $php);
        foreach (['OwnerSourceRuntime', 'PostgreSql', 'Mapper', 'PDO', 'Illuminate\\', 'Infrastructure\\', 'RuntimeHealth', 'ListingLifecycle', 'App\\Http', 'Event\\', 'Delivery\\', 'Outbox\\', 'SearchDocumentId', 'SearchIndexId', 'ListingId'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_no_provider_or_binding_was_added(): void
    {
        self::assertFileDoesNotExist(dirname(__DIR__, 2).'/app/Providers/SearchQueryResolutionOwnerReaderServiceProvider.php');
    }
}

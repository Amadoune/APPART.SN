<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PublicSearchResultsReadModelArchitectureTest extends TestCase
{
    public function test_application_contract_is_read_only_and_infrastructure_free(): void
    {
        $directory = dirname(__DIR__, 2).'/app/Application/PublicSearchResults';
        $sources = $this->sources($directory);

        self::assertStringContainsString('interface PublicSearchResultsReaderV1', $sources);
        self::assertStringContainsString('public function read(', $sources);
        foreach (['PDO', 'Infrastructure\\', 'Illuminate\\', 'Domain\\', 'Aggregate'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $sources);
        }
    }

    public function test_http_surface_has_no_sql_or_projection_source_access(): void
    {
        $sources = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/PublicSearchResultsController.php')
            .file_get_contents(dirname(__DIR__, 2).'/app/Http/Requests/PublicSearchResultsRequest.php')
            .file_get_contents(dirname(__DIR__, 2).'/app/Http/PublicSearchResults/PublicSearchResultsResponseFactory.php');

        foreach (['PDO', 'SELECT ', 'listing_projections', 'OwnerSource', 'Aggregate', 'Repository'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $sources);
        }
    }

    private function sources(string $directory): string
    {
        $sources = '';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $sources .= file_get_contents($file->getPathname());
            }
        }

        return $sources;
    }
}

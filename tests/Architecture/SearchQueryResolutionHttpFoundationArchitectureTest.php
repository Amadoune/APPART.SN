<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class SearchQueryResolutionHttpFoundationArchitectureTest extends TestCase
{
    public function test_controller_depends_on_public_reader_only(): void
    {
        $files = [
            dirname(__DIR__, 2).'/app/Http/Controllers/SearchQueryResolutionController.php',
            dirname(__DIR__, 2).'/app/Http/Requests/SearchQueryResolutionRequest.php',
            dirname(__DIR__, 2).'/app/Http/SearchQueryResolution/SearchQueryResolutionResponseFactory.php',
            dirname(__DIR__, 2).'/app/Http/SearchQueryResolution/SearchQueryResolutionHttpRuntimeV1.php',
        ];
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), $files));

        self::assertStringContainsString('PublicSearchQueryResolutionReaderV1', $php);
        foreach (['SearchQueryResolutionOwnerReader', 'SearchQueryResolutionOwnerSource', 'OwnerSourceRuntime', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'RuntimeHealth', 'ListingLifecycle', 'Projection', 'Event\\', 'Delivery\\', 'Outbox\\', 'SearchDocumentId', 'SearchIndexId', 'ListingId'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }
}

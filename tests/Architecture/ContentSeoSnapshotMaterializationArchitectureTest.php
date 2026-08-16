<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ContentSeoSnapshotMaterializationArchitectureTest extends TestCase
{
    public function test_application_materialization_is_projection_free_and_has_no_sql_or_clock(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContentSeo/Application/Materialization';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = (string) file_get_contents($file->getPathname());
            foreach (['PublicListingProjection', 'listing_projections', 'SearchDecisionWriter', 'random_bytes', 'clock_timestamp', 'SELECT ', 'INSERT ', 'UPDATE ', 'DELETE '] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getFilename());
            }
        }
    }

    public function test_catchup_reuses_the_productive_materializer_and_no_migration_is_added(): void
    {
        $root = dirname(__DIR__, 2);
        $catchUp = (string) file_get_contents($root.'/src/Modules/ContentSeo/Application/Materialization/DeterministicCatchUpContentSeoSnapshotV1.php');
        self::assertStringContainsString('MaterializeContentSeoSnapshotV1', $catchUp);
        self::assertStringContainsString('materializer->materialize', $catchUp);
        self::assertFileDoesNotExist($root.'/src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/Migrations/101_content_seo_snapshot_materialization.sql');
    }
}

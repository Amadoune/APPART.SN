<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AuthoringPublicFactHandoffArchitectureTest extends TestCase
{
    public function test_search_reads_public_projection_and_never_authoring_state(): void
    {
        $reader = $this->contents('app/Infrastructure/PublicProjectionStore/PostgreSql/PostgreSqlPublicSearchResultsReader.php');

        self::assertStringContainsString('public_projection.listing_projections', $reader);
        self::assertStringNotContainsString('listing_authoring', $reader);
        self::assertStringNotContainsString('ListingDraft', $reader);
        self::assertLessThan(strpos($reader, 'ORDER BY p.canonical_path'), strpos($reader, 'p.transaction_kind = :transaction'));
        self::assertLessThan(strpos($reader, 'LIMIT :limit'), strpos($reader, 'p.property_type) = lower(:property_type)'));
    }

    public function test_projection_reads_only_sealed_public_facts(): void
    {
        $source = $this->contents('app/Infrastructure/ProjectionRuntimeSource/CertifiedPublicListingProjectionSource.php');

        self::assertStringContainsString('AuthoringPublicFactHandoffV1', $source);
        self::assertStringContainsString('->published(', $source);
        self::assertStringNotContainsString('ListingDraftStore', $source);
        self::assertStringNotContainsString('listing_authoring', $source);
    }

    public function test_schema_change_is_additive_and_historical_rows_remain_unqualified(): void
    {
        $migration = $this->contents('src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/093_authoring_public_fact_handoff.sql');

        self::assertStringContainsString('ADD COLUMN IF NOT EXISTS transaction_kind varchar(8) NULL', $migration);
        self::assertStringNotContainsString("DEFAULT 'sale'", $migration);
        self::assertStringNotContainsString("DEFAULT 'rent'", $migration);
        self::assertStringNotContainsString('UPDATE public_projection.listing_projections', $migration);
    }

    private function contents(string $path): string
    {
        $contents = file_get_contents(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path));
        self::assertIsString($contents);

        return $contents;
    }
}

<?php

namespace Tests\Architecture;

use Appart\Modules\ContentSeo\Application\Contract\HistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlHistoricalCanonicalQualifier;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class HistoricalCanonicalQualificationPostgreSqlArchitectureTest extends TestCase
{
    public function test_adapter_implements_exactly_the_certified_contract(): void
    {
        $adapter = new ReflectionClass(PostgreSqlHistoricalCanonicalQualifier::class);

        self::assertTrue($adapter->isFinal());
        self::assertTrue($adapter->isReadOnly());
        self::assertSame([HistoricalCanonicalQualifier::class], $adapter->getInterfaceNames());
    }

    public function test_slice_has_no_runtime_http_redirect_or_business_repository_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContentSeo/Infrastructure/Persistence';
        foreach ([$root.'/HistoricalCanonicalQualificationMapper.php', $root.'/PostgreSql/PostgreSqlHistoricalCanonicalQualifier.php', $root.'/PostgreSql/Migrations/014_historical_canonical_qualifications.sql'] as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            foreach (['Illuminate', 'Laravel', 'Runtime', 'PublicListingQuery', 'Repository', 'Aggregate', 'ListingId', 'HistoricalRedirectResolver', 'destination_canonical', 'redirect_target', 'now()', 'CURRENT_TIMESTAMP', 'clock_timestamp'] as $forbidden) {
                self::assertStringNotContainsStringIgnoringCase($forbidden, $contents, $file);
            }
            if (str_ends_with($file, '.php')) {
                self::assertStringNotContainsStringIgnoringCase('Http', $contents, $file);
            }
            self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents, $file);
        }
    }

    public function test_reader_is_exact_ordered_bounded_and_read_only(): void
    {
        $file = dirname(__DIR__, 2).'/src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/PostgreSqlHistoricalCanonicalQualifier.php';
        $contents = file_get_contents($file);
        self::assertIsString($contents);
        self::assertStringContainsString('WHERE canonical=:canonical', $contents);
        self::assertStringContainsString('ORDER BY decision_id', $contents);
        self::assertStringContainsString('LIMIT 2', $contents);
        self::assertSame(1, substr_count($contents, 'SELECT '));
        self::assertDoesNotMatchRegularExpression('/\b(?:INSERT|UPDATE|DELETE)\b/', $contents);
    }

    public function test_migration_preserves_ambiguity_and_contains_only_qualification_data(): void
    {
        $file = dirname(__DIR__, 2).'/src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/Migrations/014_historical_canonical_qualifications.sql';
        $contents = file_get_contents($file);
        self::assertIsString($contents);
        self::assertStringNotContainsString('UNIQUE (canonical)', $contents);
        self::assertStringContainsString("qualification IN ('current', 'historical')", $contents);
        self::assertStringContainsString('revision > 0', $contents);
        self::assertStringContainsString("decision_checksum ~ '^[0-9a-f]{64}$'", $contents);
        self::assertStringContainsString('historical_canonical_qualifications_lookup', $contents);
        foreach (['destination', 'listing_id', 'target'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }
}

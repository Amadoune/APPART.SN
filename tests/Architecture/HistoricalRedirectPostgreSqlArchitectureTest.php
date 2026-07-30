<?php

namespace Tests\Architecture;

use Appart\Modules\ContentSeo\Application\Contract\HistoricalRedirectResolver;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlHistoricalRedirectResolver;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class HistoricalRedirectPostgreSqlArchitectureTest extends TestCase
{
    public function test_adapter_implements_exactly_the_certified_port(): void
    {
        $adapter = new ReflectionClass(PostgreSqlHistoricalRedirectResolver::class);

        self::assertTrue($adapter->isFinal());
        self::assertTrue($adapter->isReadOnly());
        self::assertSame([HistoricalRedirectResolver::class], $adapter->getInterfaceNames());
    }

    public function test_slice_has_no_runtime_web_laravel_or_business_repository_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContentSeo/Infrastructure/Persistence';
        $files = [
            $root.'/HistoricalRedirectDecisionMapper.php',
            $root.'/PostgreSql/PostgreSqlHistoricalRedirectResolver.php',
            $root.'/PostgreSql/Migrations/013_historical_redirect_decisions.sql',
        ];
        foreach ($files as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            foreach (['Illuminate', 'Laravel', 'Runtime', 'PublicListingQuery', 'Repository', 'Aggregate', 'ListingId', 'now()', 'CURRENT_TIMESTAMP', 'clock_timestamp'] as $forbidden) {
                self::assertStringNotContainsStringIgnoringCase($forbidden, $contents, $file);
            }
            if (str_ends_with($file, '.php')) {
                self::assertStringNotContainsStringIgnoringCase('Http', $contents, $file);
            }
            self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents, $file);
        }
    }

    public function test_reader_is_exact_bounded_deterministic_and_does_not_follow_a_chain(): void
    {
        $file = dirname(__DIR__, 2).'/src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/PostgreSqlHistoricalRedirectResolver.php';
        $contents = file_get_contents($file);
        self::assertIsString($contents);
        self::assertStringContainsString('WHERE historical_canonical=:historical_canonical', $contents);
        self::assertStringContainsString('ORDER BY decision_id', $contents);
        self::assertStringContainsString('LIMIT 2', $contents);
        self::assertSame(1, substr_count($contents, 'SELECT '));
        self::assertDoesNotMatchRegularExpression('/\b(?:INSERT|UPDATE|DELETE)\b/', $contents);
    }

    public function test_migration_preserves_observable_ambiguity_and_public_only_shape(): void
    {
        $file = dirname(__DIR__, 2).'/src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/Migrations/013_historical_redirect_decisions.sql';
        $contents = file_get_contents($file);
        self::assertIsString($contents);
        self::assertStringNotContainsString('UNIQUE (historical_canonical)', $contents);
        self::assertStringContainsString("destination_qualification IN ('current', 'historical')", $contents);
        self::assertStringContainsString('revision > 0', $contents);
        self::assertStringContainsString("decision_checksum ~ '^[0-9a-f]{64}$'", $contents);
        self::assertStringContainsString('historical_redirect_decisions_source_lookup', $contents);
        self::assertStringNotContainsString('listing_id', $contents);
    }
}

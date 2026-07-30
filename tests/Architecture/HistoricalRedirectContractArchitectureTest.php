<?php

namespace Tests\Architecture;

use App\Application\Contract\PublicListingQuery;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalRedirectResolver;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalCanonical;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalRedirectResolution;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class HistoricalRedirectContractArchitectureTest extends TestCase
{
    public function test_resolver_accepts_only_a_declared_historical_canonical(): void
    {
        $method = new ReflectionMethod(HistoricalRedirectResolver::class, 'resolve');

        self::assertSame(HistoricalCanonical::class, (string) $method->getParameters()[0]->getType());
        self::assertSame(HistoricalRedirectResolution::class, (string) $method->getReturnType());
    }

    public function test_foundation_is_content_seo_owned_and_dependency_free(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContentSeo/Application';
        $files = array_merge(
            glob($root.'/HistoricalRedirect/{,Exception/}*.php', GLOB_BRACE) ?: [],
            [$root.'/Contract/HistoricalRedirectResolver.php'],
        );

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            foreach (['Illuminate', 'Laravel', '\\Infrastructure\\', 'PostgreSql', 'PDO', 'SQL', 'Http', 'Runtime', 'Repository', 'Aggregate', 'ListingId'] as $forbidden) {
                self::assertStringNotContainsStringIgnoringCase($forbidden, $contents, $file);
            }
            self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents, $file);
        }
    }

    public function test_public_listing_query_remains_current_only_and_unchanged_in_scope(): void
    {
        $contract = new ReflectionMethod(PublicListingQuery::class, 'findByCanonicalPath');
        self::assertSame('string', (string) $contract->getParameters()[0]->getType());
        self::assertCount(1, (new \ReflectionClass(PublicListingQuery::class))->getMethods());

        $contents = file_get_contents(dirname(__DIR__, 2).'/app/Application/Contract/PublicListingQuery.php');
        self::assertIsString($contents);
        self::assertStringContainsString('exact current public canonical path', $contents);
        self::assertStringContainsString('Historical paths and listing identifiers are not fallback identities', $contents);
        self::assertStringNotContainsString('HistoricalRedirect', $contents);
    }

    public function test_no_alternative_infrastructure_or_specialized_provider_was_added(): void
    {
        $root = dirname(__DIR__, 2);
        foreach (['app/Infrastructure', 'app/Providers', 'database'] as $directory) {
            $matches = glob($root.'/'.$directory.'/*HistoricalRedirect*', GLOB_NOSORT) ?: [];
            self::assertSame([], $matches, $directory);
        }
    }
}

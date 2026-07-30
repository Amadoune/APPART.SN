<?php

namespace Tests\Architecture;

use App\Application\Contract\PublicListingQuery;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification\HistoricalCanonicalQualification;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class HistoricalCanonicalQualificationContractArchitectureTest extends TestCase
{
    public function test_qualifier_accepts_a_public_canonical_and_returns_a_closed_qualification(): void
    {
        $method = new ReflectionMethod(HistoricalCanonicalQualifier::class, 'qualify');

        self::assertSame(CanonicalUrl::class, (string) $method->getParameters()[0]->getType());
        self::assertSame(HistoricalCanonicalQualification::class, (string) $method->getReturnType());
    }

    public function test_foundation_is_content_seo_owned_and_infrastructure_free(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContentSeo/Application';
        $files = array_merge(
            glob($root.'/HistoricalCanonicalQualification/*.php') ?: [],
            [$root.'/Contract/HistoricalCanonicalQualifier.php'],
        );
        foreach ($files as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            foreach (['Illuminate', 'Laravel', '\\Infrastructure\\', 'PostgreSql', 'PDO', 'SQL', 'Http', 'Runtime', 'Repository', 'Aggregate', 'PublicListingQuery', 'HistoricalRedirectResolver'] as $forbidden) {
                self::assertStringNotContainsStringIgnoringCase($forbidden, $contents, $file);
            }
            self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents, $file);
        }
    }

    public function test_no_infrastructure_runtime_or_http_artifact_was_created(): void
    {
        $root = dirname(__DIR__, 2);
        foreach (['app/Infrastructure', 'app/Http', 'app/Providers', 'database', 'routes', 'src/Modules/ContentSeo/Infrastructure'] as $directory) {
            self::assertSame([], glob($root.'/'.$directory.'/*HistoricalCanonicalQualif*') ?: [], $directory);
        }
    }

    public function test_public_listing_query_remains_current_only(): void
    {
        $contract = new \ReflectionClass(PublicListingQuery::class);
        self::assertCount(1, $contract->getMethods());
        $contents = file_get_contents($contract->getFileName());
        self::assertIsString($contents);
        self::assertStringContainsString('exact current public canonical path', $contents);
        self::assertStringNotContainsString('HistoricalCanonicalQualifier', $contents);
    }
}

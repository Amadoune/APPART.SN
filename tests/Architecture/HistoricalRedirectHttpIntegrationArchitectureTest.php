<?php

namespace Tests\Architecture;

use App\Application\Contract\PublicListingQuery;
use App\Http\Controllers\PublicListingController;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalRedirectResolver;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;

final class HistoricalRedirectHttpIntegrationArchitectureTest extends TestCase
{
    public function test_controller_depends_only_on_the_three_application_ports_and_logger(): void
    {
        $constructor = (new ReflectionClass(PublicListingController::class))->getConstructor();
        self::assertNotNull($constructor);
        self::assertSame(
            [PublicListingQuery::class, HistoricalCanonicalQualifier::class, HistoricalRedirectResolver::class, LoggerInterface::class],
            array_map(static fn ($parameter): string => (string) $parameter->getType(), $constructor->getParameters()),
        );
    }

    public function test_controller_contains_no_sql_domain_repository_seo_or_chain_following(): void
    {
        $file = dirname(__DIR__, 2).'/app/Http/Controllers/PublicListingController.php';
        $contents = file_get_contents($file);
        self::assertIsString($contents);
        foreach (['PDO', 'Repository', 'Registry', 'Aggregate', 'ListingId', 'CanonicalPolicy', 'ListingSeoDecisionPolicy', 'SELECT ', 'INSERT ', 'UPDATE ', 'DELETE '] as $forbidden) {
            self::assertStringNotContainsStringIgnoringCase($forbidden, $contents, $file);
        }
        self::assertSame(1, substr_count($contents, '$this->redirects->resolve('));
        self::assertSame(1, substr_count($contents, '$this->qualifier->qualify('));
        self::assertStringNotContainsString('HistoricalCanonical::declared', $contents);
        self::assertStringNotContainsString('HistoricalRedirectTarget::', $contents);
        self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents);
    }

    public function test_all_certified_statuses_are_mapped_explicitly(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/PublicListingController.php');
        self::assertIsString($contents);
        foreach (['Current', 'Historical', 'Unknown', 'Ambiguous', 'Corrupted'] as $status) {
            self::assertStringContainsString('HistoricalCanonicalQualificationStatus::'.$status, $contents);
        }
        foreach (['Resolved', 'NotFound', 'DestinationMissing', 'LoopDetected', 'ChainDetected', 'Ambiguous', 'Corrupted'] as $status) {
            self::assertStringContainsString('HistoricalRedirectStatus::'.$status, $contents);
        }
    }

    public function test_redirect_uses_only_certified_target_and_explicit_301(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/PublicListingController.php');
        self::assertIsString($contents);
        self::assertStringContainsString('new RedirectResponse($resolution->target?->canonical->value', $contents);
        self::assertStringContainsString(', 301)', $contents);
        self::assertDoesNotMatchRegularExpression('/(?:url\(|route\(|redirect\(\)|Location)/', $contents);
    }

    public function test_public_listing_query_remains_exactly_current_only(): void
    {
        $contract = new ReflectionClass(PublicListingQuery::class);
        self::assertCount(1, $contract->getMethods());
        $contents = file_get_contents($contract->getFileName());
        self::assertIsString($contents);
        self::assertStringContainsString('exact current public canonical path', $contents);
        self::assertStringNotContainsString('Historical', (string) $contract->getMethod('findByCanonicalPath')->getReturnType());
    }
}

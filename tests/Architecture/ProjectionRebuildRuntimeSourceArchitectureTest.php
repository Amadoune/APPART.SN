<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ProjectionRebuildRuntimeSourceArchitectureTest extends TestCase
{
    public function test_application_results_have_no_infrastructure_framework_or_sql_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/ProjectionRebuildRuntimeSource';
        $files = [...(glob($root.'/*.php') ?: []), ...(glob($root.'/Contract/*.php') ?: [])];
        foreach ($files as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/(?:Illuminate|Laravel|Infrastructure|\bPDO\b|\bSELECT\b|\bINSERT\b|Http)/i', $contents);
        }
    }

    public function test_enumerator_is_bounded_ordered_and_read_only(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/app/Infrastructure/ProjectionRebuildRuntimeSource/PostgreSql/PostgreSqlPublicProjectionRebuildEnumerator.php');
        self::assertIsString($contents);
        self::assertStringContainsString('ORDER BY id LIMIT ', $contents);
        self::assertStringContainsString('$limit + 1', $contents);
        self::assertDoesNotMatchRegularExpression('/\b(?:INSERT|UPDATE|DELETE|CREATE|ALTER|DROP)\b/i', $contents);
        self::assertDoesNotMatchRegularExpression('/(?:Http|Controller|Laravel|Illuminate)/i', $contents);
    }

    public function test_candidate_factory_delegates_to_certified_components_without_persistence_or_clock(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/app/Infrastructure/ProjectionRebuildRuntimeSource/CertifiedPublicProjectionCandidateFactory.php');
        self::assertIsString($contents);
        foreach (['CandidatePublicListingProjectionSource', 'SearchListingProjectionBuilder', 'ListingSeoDecisionPolicy', 'SeoListingProjectionBuilder', 'PublicListingReadModelBuilder'] as $dependency) {
            self::assertStringContainsString($dependency, $contents);
        }
        self::assertDoesNotMatchRegularExpression('/(?:\bnow\s*\(|CURRENT_TIMESTAMP|clock_timestamp|\bPDO\b|\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b|Http|Laravel|Illuminate)/i', $contents);
    }
}

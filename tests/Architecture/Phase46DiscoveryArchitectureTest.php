<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Phase46DiscoveryArchitectureTest extends TestCase
{
    /** @return iterable<string, array{string, string}> */
    public static function documents(): iterable
    {
        yield 'analysis' => ['PHASE-4.6-CAPABILITY-CANDIDATE-ANALYSIS.md', 'Media Item Lifecycle'];
        yield 'comparison' => ['PHASE-4.6-CANDIDATE-COMPARISON-MATRIX.md', 'Moderation Case Lifecycle'];
        yield 'decision' => ['PHASE-4.6-CAPABILITY-DECISION.md', 'Propriétaire unique : **Media**'];
        yield 'blueprint' => ['PHASE-4.6-CONTRACT-BLUEPRINT.md', '| Active | Removed / Allowed |'];
        yield 'dependencies' => ['PHASE-4.6-DEPENDENCY-MATRIX.md', 'MediaCollectionRegistry'];
        yield 'risks' => ['PHASE-4.6-RISK-MATRIX.md', 'média principal'];
        yield 'roadmap' => ['PHASE-4.6-ROADMAP.md', '4.6C-R1'];
        yield 'certification' => ['PHASE-4.6-DISCOVERY-CERTIFICATION.md', 'GO proposé'];
    }

    #[DataProvider('documents')]
    public function test_the_discovery_is_complete_and_explicit(string $file, string $proof): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/docs/'.$file);
        self::assertIsString($contents);
        self::assertStringContainsString($proof, $contents);
    }

    public function test_the_blueprint_preserves_the_collection_boundary(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/docs/PHASE-4.6-CONTRACT-BLUEPRINT.md');
        self::assertIsString($contents);
        self::assertStringContainsString('MediaCollection` reste propriétaire', $contents);
        self::assertStringContainsString('ne pourra jamais recalculer', $contents);
        self::assertStringContainsString('création', $contents);
        self::assertStringContainsString('hors workflow', $contents);
    }
}

<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Phase47DiscoveryArchitectureTest extends TestCase
{
    /** @return iterable<string, array{string, string}> */
    public static function documents(): iterable
    {
        yield 'analysis' => ['PHASE-4.7-CAPABILITY-CANDIDATE-ANALYSIS.md', 'Administrative Action Lifecycle'];
        yield 'comparison' => ['PHASE-4.7-CANDIDATE-COMPARISON-MATRIX.md', 'Account Status Lifecycle'];
        yield 'decision' => ['PHASE-4.7-CAPABILITY-DECISION.md', 'Propriétaire unique : **AdministrationAudit**'];
        yield 'blueprint' => ['PHASE-4.7-CONTRACT-BLUEPRINT.md', 'IndependentApprovalRequired'];
        yield 'dependencies' => ['PHASE-4.7-DEPENDENCY-MATRIX.md', 'registre PostgreSQL historique'];
        yield 'risks' => ['PHASE-4.7-RISK-MATRIX.md', 'auto-approbation'];
        yield 'gates' => ['PHASE-4.7-PREVENTIVE-GATES.md', 'NO GO immédiat'];
        yield 'roadmap' => ['PHASE-4.7-ROADMAP.md', '4.7A-R1'];
        yield 'certification' => ['PHASE-4.7-DISCOVERY-CERTIFICATION.md', 'GO proposé'];
    }

    #[DataProvider('documents')]
    public function test_the_discovery_is_complete_and_explicit(string $file, string $proof): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/docs/'.$file);
        self::assertIsString($contents);
        self::assertStringContainsString($proof, $contents);
    }

    public function test_the_blueprint_prevents_implicit_policy_reconstruction(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/docs/PHASE-4.7-CONTRACT-BLUEPRINT.md');
        self::assertIsString($contents);
        self::assertStringContainsString('ne peut pas honnêtement dépendre du seul couple', $contents);
        self::assertStringContainsString('sans appeler `FourEyesPolicy`', $contents);
        self::assertStringContainsString('sans lire l\'Aggregate', $contents);
        self::assertStringContainsString('La création produit explicitement', $contents);
    }
}

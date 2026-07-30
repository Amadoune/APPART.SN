<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class Phase45DiscoveryArchitectureTest extends TestCase
{
    public function test_discovery_selects_one_bounded_capability_and_prevents_scope_leakage(): void
    {
        $root = dirname(__DIR__, 2).'/docs/';
        $discovery = (string) file_get_contents($root.'PHASE-4.5-CAPABILITY-DISCOVERY.md');
        $blueprint = (string) file_get_contents($root.'PHASE-4.5-CONTRACT-BLUEPRINT.md');
        $gates = (string) file_get_contents($root.'PHASE-4.5-PREVENTIVE-GATES.md');
        $roadmap = (string) file_get_contents($root.'PHASE-4.5-ROADMAP.md');

        self::assertStringContainsString('Professional Status Lifecycle', $discovery);
        self::assertStringContainsString('établissements ni celui des mandats', $discovery);
        self::assertStringContainsString('initialState()` retourne `Active`', $blueprint);
        self::assertStringContainsString('port non-void', $gates);
        self::assertStringContainsString('Professionals → professionals', $roadmap);
    }
}

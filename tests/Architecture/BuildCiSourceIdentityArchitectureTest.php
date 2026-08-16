<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class BuildCiSourceIdentityArchitectureTest extends TestCase
{
    private const CANDIDATE_TAG = 'appart-sn-release-candidate-rc2-r3';

    private const SOURCE_BASE = '6fc2f7944368c65f0cd87dfa68f550faa3461be9';

    private const PREDECESSOR_TAG = 'appart-sn-release-candidate-rc2-r2';

    private const R5_CANDIDATE_TAG = 'phase-5.9-baseline-candidate-r5';

    private const R5_SOURCE_BASE = '058719f8aa154466056299b8c26bd7d51f944127';

    public function test_runtime_lock_expresses_the_non_self_referential_identity_policy(): void
    {
        $lock = json_decode($this->read('build/runtime.lock.json'), true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('appart.runtime-lock.v2', $lock['schema']);
        self::assertSame(self::SOURCE_BASE, $lock['sourceBaseCommit']);
        self::assertSame(self::CANDIDATE_TAG, $lock['candidateTag']);
        self::assertSame(
            'annotated-candidate-tag-resolves-head-and-source-base-is-ancestor',
            $lock['identityPolicy'],
        );
        self::assertArrayNotHasKey('sourceCommit', $lock);
        self::assertArrayNotHasKey('sourceTag', $lock);
    }

    public function test_workflow_and_packaging_share_the_exact_identity_policy(): void
    {
        $workflow = $this->read('.github/workflows/phase-5.9-reproducible-build.yml');
        $packaging = $this->read('tools/release/build-release.sh');

        foreach ([$workflow, $packaging] as $control) {
            self::assertStringContainsString(self::SOURCE_BASE, $control);
            self::assertStringContainsString(self::CANDIDATE_TAG, $control);
            self::assertStringContainsString('git cat-file -t', $control);
            self::assertStringContainsString('git merge-base --is-ancestor', $control);
            self::assertStringNotContainsString(self::R5_CANDIDATE_TAG, $control);
            self::assertStringNotContainsString(self::R5_SOURCE_BASE, $control);
        }

        self::assertStringContainsString('= "$GITHUB_SHA"', $workflow);
        self::assertStringContainsString('= "$BUILD_SHA"', $packaging);
    }

    public function test_workflow_trigger_is_not_a_permissive_candidate_wildcard(): void
    {
        $workflow = $this->read('.github/workflows/phase-5.9-reproducible-build.yml');

        self::assertStringContainsString("- '".self::CANDIDATE_TAG."'", $workflow);
        self::assertStringNotContainsString('phase-5.9-baseline-candidate-*', $workflow);
        self::assertStringNotContainsString('phase-5.9-build-ci-rc*', $workflow);
    }

    public function test_predecessor_tag_is_rejected_as_the_future_candidate(): void
    {
        $controls = $this->controls();

        foreach ($controls as $control) {
            self::assertStringNotContainsString(self::PREDECESSOR_TAG."'", $control);
            self::assertStringNotContainsString(self::PREDECESSOR_TAG.'"', $control);
        }
    }

    public function test_wrong_predecessor_and_future_tag_are_rejected(): void
    {
        foreach ($this->controls() as $control) {
            self::assertStringNotContainsString(str_repeat('0', 40), $control);
            self::assertStringNotContainsString('appart-sn-release-candidate-rc2-r4', $control);
        }
    }

    public function test_cross_file_identity_is_exactly_consistent(): void
    {
        foreach ($this->controls() as $control) {
            self::assertStringContainsString(self::SOURCE_BASE, $control);
            self::assertStringContainsString(self::CANDIDATE_TAG, $control);
        }
    }

    public function test_frontend_production_build_precedes_every_test_suite(): void
    {
        $workflow = $this->read('.github/workflows/phase-5.9-reproducible-build.yml');
        preg_match_all('/^      - name: (.+)$/m', $workflow, $matches, PREG_OFFSET_CAPTURE);

        $steps = [];
        foreach ($matches[1] as [$name, $offset]) {
            $steps[$name] = $offset;
        }

        self::assertArrayHasKey('Restore locked dependencies', $steps);
        self::assertArrayHasKey('Frontend production build', $steps);
        self::assertArrayHasKey('Unit, Feature, Architecture and Foundation gates', $steps);
        self::assertArrayHasKey('PostgreSQL gate', $steps);
        self::assertArrayHasKey('Static analysis and formatting gates', $steps);
        self::assertArrayHasKey('Build deterministic release artifact', $steps);
        self::assertLessThan($steps['Frontend production build'], $steps['Restore locked dependencies']);
        self::assertLessThan($steps['Unit, Feature, Architecture and Foundation gates'], $steps['Frontend production build']);
        self::assertLessThan($steps['PostgreSQL gate'], $steps['Frontend production build']);
        self::assertLessThan($steps['Static analysis and formatting gates'], $steps['Frontend production build']);
        self::assertLessThan($steps['Build deterministic release artifact'], $steps['Frontend production build']);
        self::assertSame(1, substr_count($workflow, 'run: npm run build'));
    }

    /** @return list<string> */
    private function controls(): array
    {
        return [
            $this->read('build/runtime.lock.json'),
            $this->read('.github/workflows/phase-5.9-reproducible-build.yml'),
            $this->read('tools/release/build-release.sh'),
        ];
    }

    private function read(string $relativePath): string
    {
        $contents = file_get_contents(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.$relativePath);

        self::assertIsString($contents);

        return $contents;
    }
}

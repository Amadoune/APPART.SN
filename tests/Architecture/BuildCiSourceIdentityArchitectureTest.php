<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class BuildCiSourceIdentityArchitectureTest extends TestCase
{
    private const CANDIDATE_TAG = 'phase-5.9-baseline-candidate-r3';

    private const SOURCE_BASE = '5b1d0e647d1f74629b5f7e99e6f9d7e31941e988';

    private const R1 = '1337e225c63e6a3e25c5926f7c4fbddb4ba24da7';

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
            self::assertStringNotContainsString(self::R1, $control);
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

    private function read(string $relativePath): string
    {
        $contents = file_get_contents(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.$relativePath);

        self::assertIsString($contents);

        return $contents;
    }
}

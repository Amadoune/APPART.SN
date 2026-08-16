<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AuthoringDraftResumeArchitectureTest extends TestCase
{
    public function test_resume_application_is_read_only_and_does_not_cross_closed_boundaries(): void
    {
        $root = dirname(__DIR__, 2);
        $source = (string) file_get_contents($root.'/app/Application/AuthoringDraftResume/DeterministicAuthoringDraftResumeReaderV1.php');

        foreach (['PDO', 'prepare(', 'query(', 'save(', 'add(', 'append(', 'Projection', 'Search', 'Promotion'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
        self::assertStringContainsString('authoringPortfolio()->listFor($accountId)', $source);
        self::assertStringContainsString('listingOwnership()->read($listingId)', $source);
    }

    public function test_frontend_has_no_authoritative_browser_storage_and_does_not_generate_ids_in_resume_branch(): void
    {
        $script = (string) file_get_contents(dirname(__DIR__, 2).'/resources/js/authoring.js');

        self::assertStringNotContainsString('localStorage', $script);
        self::assertStringNotContainsString('sessionStorage', $script);
        self::assertStringContainsString("resume?.mode === 'resume'", $script);
        self::assertStringContainsString(': { step: 1, propertyId: crypto.randomUUID()', $script);
    }

    public function test_no_resume_migration_exists(): void
    {
        self::assertSame([], glob(dirname(__DIR__, 2).'/**/Migrations/*resume*') ?: []);
    }
}

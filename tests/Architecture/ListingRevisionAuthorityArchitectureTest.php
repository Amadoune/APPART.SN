<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ListingRevisionAuthorityArchitectureTest extends TestCase
{
    public function test_revision_authority_stays_owner_scoped_and_infrastructure_free(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Application/RevisionAuthority';
        $source = implode("\n", array_map(
            static fn (string $file): string => (string) file_get_contents($file),
            glob($root.'/*.php') ?: [],
        ));

        foreach (['Illuminate', 'Http', 'PDO', 'PostgreSql', 'Repository', 'DB::', 'random', 'time(', 'now('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
        self::assertStringContainsString("private const string SCOPE = 'appart.listing-lifecycle.revision.v1'", $source);
    }
}

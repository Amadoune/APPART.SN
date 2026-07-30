<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ProfessionalStatusEventContractArchitectureTest extends TestCase
{
    public function test_event_foundation_is_closed_and_infrastructure_free(): void
    {
        $root = dirname(__DIR__, 2);
        $files = glob($root.'/src/Modules/Professionals/Application/ProfessionalStatusEvent/*.php') ?: [];
        self::assertCount(8, $files);
        $source = implode("\n", array_map(static fn (string $file): string => (string) file_get_contents($file), $files));

        foreach (['PostgreSql', 'PDO', 'Laravel', 'Illuminate', 'Repository', 'Migration', 'Transport', 'Inbox', 'Outbox', 'Consumer', 'Worker', 'Http', 'ListingCatalog', 'AdvertiserCatalog', 'now(', 'random'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_no_runtime_binding_or_publication_is_introduced(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertStringNotContainsString('ProfessionalStatusEventCatalog::class', $provider);
        self::assertStringNotContainsString('ProfessionalStatusEventSerializer::class', $provider);
        self::assertSame([], glob($root.'/app/**/*ProfessionalStatus*Event*.php') ?: []);
    }
}

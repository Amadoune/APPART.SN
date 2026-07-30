<?php

namespace Tests\Architecture;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ListingModerationVersionReadAmendmentArchitectureTest extends TestCase
{
    #[Test]
    public function existing_store_is_an_owner_application_port_and_requires_no_new_persistence(): void
    {
        $reflection = new ReflectionClass(ListingPublicationWorkflowStore::class);
        $source = (string) file_get_contents((string) $reflection->getFileName());

        self::assertTrue($reflection->isInterface());
        self::assertStringContainsString('\\ListingLifecycle\\Application\\', $reflection->getName());
        self::assertTrue($reflection->hasMethod('read'));

        foreach (['PDO', 'PostgreSql', 'Illuminate', 'Infrastructure'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    #[Test]
    public function amendment_adds_no_migration_or_version_reader_contract(): void
    {
        $root = dirname(__DIR__, 2);

        self::assertFileDoesNotExist(
            $root.'/src/Modules/ListingLifecycle/Application/ModerationBoundary/ListingPublicationVersionReaderV1.php',
        );
        self::assertSame(
            [],
            glob($root.'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/069*') ?: [],
        );
    }
}

<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ListingModerationIntentSourceArchitectureTest extends TestCase
{
    #[Test]
    public function source_is_owner_local_additive_append_only_and_framework_confined(): void
    {
        $root = dirname(__DIR__, 2);
        $migration = (string) file_get_contents(
            $root.'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/068_listing_moderation_intents.sql',
        );
        $application = '';
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
            $root.'/src/Modules/ListingLifecycle/Application/ModerationIntent',
        ));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $application .= (string) file_get_contents($file->getPathname());
            }
        }
        foreach (['FOREIGN KEY', 'CASCADE', 'TRIGGER', 'UPDATE ', 'DELETE '] as $forbidden) {
            self::assertStringNotContainsString($forbidden, strtoupper($migration));
        }
        foreach (['PDO', 'PostgreSql', 'Illuminate', 'Infrastructure'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $application);
        }
        self::assertStringContainsString('moderation_command_intents', $migration);
        self::assertStringContainsString('moderation_command_intent_results', $migration);
    }
}

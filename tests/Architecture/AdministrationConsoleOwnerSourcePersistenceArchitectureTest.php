<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AdministrationConsoleOwnerSourcePersistenceArchitectureTest extends TestCase
{
    #[Test]
    public function persistence_is_owner_scoped_and_has_no_forbidden_surface(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/AdministrationConsole';
        $sources = '';
        foreach ([$root.'/Application/OwnerSource', $root.'/Infrastructure/Persistence'] as $directory) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $sources .= (string) file_get_contents($file->getPathname());
                }
            }
        }
        foreach (['Modules\\IdentityAccess', 'Modules\\Moderation', 'Modules\\Notifications', 'Modules\\ContentSeo', 'Modules\\AdministrationAudit', 'Application\\Http', 'Application\\Event', 'Application\\Delivery', 'Application\\Outbox', 'Application\\Runtime', 'ServiceProvider'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $sources);
        }
        self::assertFileExists($root.'/Infrastructure/Persistence/PostgreSql/Migrations/082_administration_console_owner_source.sql');
        self::assertFileExists($root.'/Infrastructure/Persistence/PostgreSql/Migrations/082_administration_console_owner_source.down.sql');
    }
}

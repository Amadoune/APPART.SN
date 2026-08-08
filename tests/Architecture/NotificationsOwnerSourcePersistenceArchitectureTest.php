<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NotificationsOwnerSourcePersistenceArchitectureTest extends TestCase
{
    #[Test]
    public function persistence_is_owner_scoped_and_has_no_forbidden_surface(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Notifications';
        $sources = '';
        $persistenceRoots = [
            $root.'/Application/OwnerSource',
            $root.'/Infrastructure/Persistence',
        ];
        foreach ($persistenceRoots as $persistenceRoot) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($persistenceRoot)) as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $sources .= (string) file_get_contents($file->getPathname());
                }
            }
        }
        foreach (['ListingLifecycle', 'IdentityAccess', 'Moderation', 'Reservation', 'ContentSeo', 'SearchDiscovery', 'Controller', 'ServiceProvider', 'Event', 'Delivery', 'Outbox', 'Transport', 'Routing'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $sources);
        }
        self::assertFileExists($root.'/Infrastructure/Persistence/PostgreSql/Migrations/079_notifications_owner_source.sql');
        self::assertFileExists($root.'/Infrastructure/Persistence/PostgreSql/Migrations/079_notifications_owner_source.down.sql');
    }
}

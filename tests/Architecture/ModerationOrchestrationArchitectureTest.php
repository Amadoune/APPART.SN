<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationOrchestrationArchitectureTest extends TestCase
{
    #[Test]
    public function orchestration_is_owner_local_and_uses_no_forbidden_boundary(): void
    {
        $root = dirname(__DIR__, 2);
        $source = '';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root.'/app/Application/ModerationOrchestration'));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $source .= (string) file_get_contents($file->getPathname());
            }
        }
        foreach ([
            'App\\Infrastructure', 'Illuminate\\', 'PDO', 'SQLSTATE', 'IdentityAccess',
            'ListingLifecycle', 'Media\\', 'Professionals\\', 'AdministrationAudit',
            'Controller', 'Route', 'Outbox', 'Delivery',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
        self::assertStringContainsString('ModerationRuntimeV1', $source);
        self::assertStringContainsString('ModerationQueueClaimResult::DivergentIntent', $source);
        self::assertSame(1, substr_count((string) file_get_contents($root.'/bootstrap/providers.php'), 'ModerationOrchestrationServiceProvider::class'));
        self::assertFileDoesNotExist($root.'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/065_moderation_orchestration.sql');
    }
}

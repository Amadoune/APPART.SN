<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ProfessionalProfilePersistenceArchitectureTest extends TestCase
{
    public function test_persistence_is_owner_scoped_additive_and_runtime_free(): void
    {
        $root = dirname(__DIR__, 2);
        $migration = (string) file_get_contents($root.'/src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/Migrations/061_professional_profile.sql');
        self::assertStringContainsString('CREATE SCHEMA IF NOT EXISTS professional_profile', $migration);
        self::assertStringNotContainsString('FOREIGN KEY', strtoupper($migration));
        self::assertStringNotContainsString('CASCADE', strtoupper($migration));

        $files = glob($root.'/src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/PostgreSqlProfessional*Store.php');
        self::assertIsArray($files);
        self::assertCount(3, $files);
        foreach ($files as $file) {
            $source = (string) file_get_contents($file);
            self::assertStringNotContainsString('IdentityAccess', $source);
            self::assertStringNotContainsString('ProfessionalStatus', $source);
            self::assertStringNotContainsString('ListingLifecycle', $source);
            self::assertStringNotContainsString('Illuminate', $source);
        }
        foreach (['routes', 'app/Http'] as $path) {
            foreach (glob($root.'/'.$path.'/*ProfessionalProfile*') ?: [] as $forbidden) {
                self::fail('Forbidden HTTP artifact: '.$forbidden);
            }
        }
    }
}

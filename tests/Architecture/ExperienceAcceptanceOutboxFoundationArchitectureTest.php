<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ExperienceAcceptanceOutboxFoundationArchitectureTest extends TestCase
{
    public function test_outbox_is_owner_scoped_and_consumes_only_deliveries(): void
    {
        $root = dirname(__DIR__, 2);
        $php = '';
        foreach ([$root.'/src/Modules/ExperienceAcceptance/Application/Outbox', $root.'/src/Modules/ExperienceAcceptance/Infrastructure/Outbox'] as $path) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $php .= (string) file_get_contents($file->getPathname());
                }
            }
        }
        self::assertStringContainsString("OWNER = 'ExperienceAcceptance'", $php);
        foreach (['ResponsiveCompliance', 'AccessibilityCompliance', 'UserExperience', 'EndToEndReadiness', 'PerformanceReadiness', 'UserAcceptance', 'ReleaseCandidate'] as $family) {
            self::assertStringContainsString($family.'DeliveryV1', $php);
        }
        foreach (['EventV1', 'ReaderV1', 'OwnerSource', 'Transport', 'Routing', 'Consumer', 'App\\Http', 'Controller', 'Request', 'RuntimeRead', 'OwnerReader', 'Application\\Runtime', 'PublicRead\\Contract'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_concurrency_retry_and_schema_are_explicit(): void
    {
        $root = dirname(__DIR__, 2);
        $repository = (string) file_get_contents($root.'/src/Modules/ExperienceAcceptance/Infrastructure/Outbox/PostgreSqlExperienceAcceptanceOutboxRepository.php');
        $policy = (string) file_get_contents($root.'/src/Modules/ExperienceAcceptance/Application/Outbox/ExperienceAcceptanceOutboxPolicy.php');
        foreach (['ROLLBACK TO SAVEPOINT', 'FOR UPDATE OF s SKIP LOCKED', 'ON CONFLICT DO NOTHING'] as $guarantee) {
            self::assertStringContainsString($guarantee, $repository);
        }
        self::assertStringContainsString('MAX_ATTEMPTS = 10', $policy);
        self::assertStringContainsString("SAVEPOINT = 'experience_acceptance_outbox'", $policy);
        $migration = (string) file_get_contents($root.'/src/Modules/ExperienceAcceptance/Infrastructure/Outbox/Migrations/091_experience_acceptance_outbox.sql');
        foreach (['outbox_message_journal', 'outbox_message_state', "owner_name = 'ExperienceAcceptance'", 'UNIQUE', 'attempts BETWEEN 0 AND 10'] as $guarantee) {
            self::assertStringContainsString($guarantee, $migration);
        }
        self::assertStringNotContainsString('ON DELETE CASCADE', $migration);
    }

    public function test_migration_090_remains_unchanged(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ExperienceAcceptance/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('0c49f30b31338537045a2e603c7354844e871efd45c18a32b3b2fb11e7776059', hash_file('sha256', $root.'090_experience_acceptance_owner_source.sql'));
        self::assertSame('3fd63032924715010ec6a703a81ebf8ed31c38f9ce8730a7a1937af03600d79a', hash_file('sha256', $root.'090_experience_acceptance_owner_source.down.sql'));
    }
}

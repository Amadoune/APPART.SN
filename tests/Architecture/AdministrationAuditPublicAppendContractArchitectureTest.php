<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AdministrationAuditPublicAppendContractArchitectureTest extends TestCase
{
    public function test_public_append_contract_is_owner_scoped_and_framework_agnostic(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/AdministrationAudit/Application/PublicAuditAppend';
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        self::assertCount(8, $files);
        foreach ($files as $file) {
            $source = (string) file_get_contents($file);
            foreach ([
                'Illuminate\\',
                'PDO',
                'PostgreSql',
                'RuntimeHealth',
                'Repository',
                'Aggregate',
                'Lifecycle',
                'ModerationReports\\',
            ] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source, $file);
            }
        }
    }

    public function test_contract_exposes_only_append_and_has_one_owner_binding(): void
    {
        $root = dirname(__DIR__, 2);
        $contract = (string) file_get_contents(
            $root.'/src/Modules/AdministrationAudit/Application/PublicAuditAppend/Contract/AdministrationAuditAppendV1.php',
        );

        self::assertSame(1, substr_count($contract, 'public function append('));
        foreach (['read(', 'find(', 'search(', 'update(', 'delete('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contract);
        }
        self::assertFileExists(
            $root.'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/PostgreSqlAdministrationAuditAppendV1.php',
        );
        self::assertSame(
            1,
            substr_count(
                (string) file_get_contents($root.'/bootstrap/providers.php'),
                'AdministrationAuditPublicAppendServiceProvider::class',
            ),
        );
    }
}

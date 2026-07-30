<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AdministrativeActionPersistenceCoexistenceArchitectureTest extends TestCase
{
    public function test_the_contract_has_no_infrastructure_dependency(): void
    {
        $directory = dirname(__DIR__, 2).'/src/Modules/AdministrationAudit/Application/AdministrativeActionPersistenceCoexistence';
        $files = glob($directory.'/*.php');
        self::assertIsArray($files);
        self::assertCount(8, $files);

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertStringNotContainsString('Infrastructure\\', $contents);
            self::assertStringNotContainsString('PDO', $contents);
            self::assertStringNotContainsString('PostgreSql', $contents);
            self::assertStringNotContainsString('Illuminate\\', $contents);
            self::assertStringNotContainsString('Repository', $contents);
        }
    }

    public function test_the_historical_contract_repository_mapper_and_migration_are_unchanged(): void
    {
        $root = dirname(__DIR__, 2);
        self::assertSame(
            'ac274e54b8b0c63f946c3032cd6fc9d72c1536e6e108df51cb099a3e1c4969c3',
            hash_file('sha256', $root.'/src/Modules/AdministrationAudit/Application/Contract/AdministrativeActionRegistry.php'),
        );
        self::assertSame(
            'c303c7595cdce36ed1fa255d8a76d4cdc5be4eeafe54b7a02b3fda58e2f0803f',
            hash_file('sha256', $root.'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/PostgreSqlAdministrativeActionRepository.php'),
        );
        self::assertSame(
            '8aa302c830763be09f9906fd51a6bb38c14604d8328b91dd210eebc68b00100e',
            hash_file('sha256', $root.'/src/Modules/AdministrationAudit/Infrastructure/Persistence/AdministrativeActionMapper.php'),
        );
        self::assertSame(
            '0f099872948cf3a36b800f99176740efa2dd5c8e2276a9483da02b0dbfeb7885',
            hash_file('sha256', $root.'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/Migrations/001_administrative_action.sql'),
        );
    }
}

<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ActiveGenerationReaderArchitectureTest extends TestCase
{
    public function test_application_contract_has_no_infrastructure_or_runtime_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/ActiveGenerationReader';
        $files = [...(glob($root.'/*.php') ?: []), ...(glob($root.'/Contract/*.php') ?: [])];
        foreach ($files as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/(?:Illuminate|Laravel|Infrastructure|\bPDO\b|Http|Runtime)/i', $contents);
        }
    }

    public function test_postgresql_reader_is_strictly_read_only(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/app/Infrastructure/ActiveGenerationReader/PostgreSql/PostgreSqlActiveGenerationReader.php');
        self::assertIsString($contents);
        self::assertStringContainsString("WHERE state='active'", $contents);
        self::assertStringContainsString('LIMIT 2', $contents);
        self::assertDoesNotMatchRegularExpression('/\b(?:INSERT|UPDATE|DELETE|CREATE|ALTER|DROP)\b/i', $contents);
        self::assertDoesNotMatchRegularExpression('/(?:activate|rollback|candidate|promot)/i', $contents);
    }
}

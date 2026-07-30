<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PublicGeographyDurableSourceArchitectureTest extends TestCase
{
    public function test_application_source_has_no_runtime_or_infrastructure_dependency(): void
    {
        foreach (glob(dirname(__DIR__, 2).'/app/Application/PublicGeographySource/*.php') ?: [] as $file) {
            $c = file_get_contents($file);
            self::assertIsString($c);
            self::assertDoesNotMatchRegularExpression('/(?:Illuminate|Laravel|\\\\Infrastructure\\\\|\bPDO\b|Http|Runtime)/i', $c);
        }
    }

    public function test_reader_does_not_compute_geography(): void
    {
        $c = file_get_contents(dirname(__DIR__, 2).'/app/Infrastructure/PublicGeographySource/PostgreSql/PostgreSqlPublicGeographyReader.php');
        self::assertIsString($c);
        self::assertDoesNotMatchRegularExpression('/(?:breadcrumb|locality|canonical|http)/i', $c);
    }
}

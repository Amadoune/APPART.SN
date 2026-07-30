<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PublicMediaDurableSourceArchitectureTest extends TestCase
{
    public function test_application_source_has_no_runtime_or_infrastructure_dependency(): void
    {
        foreach (glob(dirname(__DIR__, 2).'/app/Application/PublicMediaSource/*.php') ?: [] as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/(?:Illuminate|Laravel|Infrastructure|\bPDO\b|Http|Runtime)/i', $contents);
        }
    }

    public function test_reader_does_not_select_or_transform_media(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/app/Infrastructure/PublicMediaSource/PostgreSql/PostgreSqlPublicMediaReader.php');
        self::assertIsString($contents);
        self::assertDoesNotMatchRegularExpression('/(?:cover|gallery|variant|sort|order|transform|http)/i', $contents);
    }
}

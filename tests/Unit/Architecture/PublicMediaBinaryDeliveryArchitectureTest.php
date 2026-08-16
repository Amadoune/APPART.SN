<?php

namespace Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

final class PublicMediaBinaryDeliveryArchitectureTest extends TestCase
{
    public function test_delivery_boundary_has_no_forbidden_projection_search_or_public_media_writer_dependency(): void
    {
        $files = array_merge(
            glob(dirname(__DIR__, 3).'/app/Application/PublicMediaBinaryDelivery/*.php') ?: [],
            glob(dirname(__DIR__, 3).'/app/Application/PublicMediaBinaryDelivery/Contract/*.php') ?: [],
            glob(dirname(__DIR__, 3).'/app/Infrastructure/PublicMediaBinaryDelivery/*.php') ?: [],
            [dirname(__DIR__, 3).'/app/Http/Controllers/PublicMediaBinaryController.php'],
        );
        self::assertNotEmpty($files);
        foreach ($files as $file) {
            $content = file_get_contents($file);
            self::assertIsString($content);
            self::assertStringNotContainsString('PublicProjection', $content, $file);
            self::assertStringNotContainsString('SearchDiscovery', $content, $file);
            self::assertStringNotContainsString('PublicMediaDecisionWriter', $content, $file);
            self::assertStringNotContainsString('storageKey', basename($file) === 'LaravelFilesystemPublicMediaBinaryContentReader.php' ? '' : $content, $file);
        }
    }
}

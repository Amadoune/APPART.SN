<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class PublicProjectionWorkerArchitectureTest extends TestCase
{
    public function test_worker_is_framework_and_infrastructure_independent(): void
    {
        $source = $this->source();

        foreach (['Illuminate\\', 'Laravel\\', 'PDO', 'App\\Infrastructure\\', 'Appart\\Modules\\', 'Controller', 'Repository', 'PublicListingProjectionUpdater'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_worker_contains_no_sql_runtime_or_business_projection_vocabulary(): void
    {
        $source = strtolower($this->source());

        foreach (['select ', 'update ', 'insert ', 'delete ', 'artisan', 'queue', 'daemon', 'canonical', 'json-ld', 'robots', 'searchprojection', 'seoprojection', 'exactly-once'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    private function source(): string
    {
        $root = dirname(__DIR__, 2).'/app/Application/PublicProjectionWorker';
        $source = '';
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $source .= file_get_contents($file->getPathname());
            }
        }

        return $source;
    }
}

<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class PublicProjectionRetryArchitectureTest extends TestCase
{
    public function test_retry_subsystem_is_contract_only_and_framework_independent(): void
    {
        $source = $this->source();

        foreach (['Illuminate\\', 'Laravel\\', 'PDO', 'App\\Infrastructure\\', 'Controller', 'Repository', 'PublicProjectionDeliveryWorker'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_retry_subsystem_contains_no_sql_or_runtime_loop(): void
    {
        $source = strtolower($this->source());

        foreach (['select ', 'update ', 'insert ', 'delete ', 'while (', 'for (;;', 'artisan', 'scheduler', 'cron', 'queue', 'http'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    private function source(): string
    {
        $root = dirname(__DIR__, 2).'/app/Application/PublicProjectionRetry';
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

<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class PublicProjectionReconciliationArchitectureTest extends TestCase
{
    public function test_reconciliation_is_purely_applicative(): void
    {
        $source = $this->source();

        foreach (['Illuminate\\', 'Laravel\\', 'PDO', 'App\\Infrastructure\\', 'Appart\\Modules\\', 'Repository', 'Controller', 'PublicListingProjectionUpdater', 'PublicProjectionDeliveryWorker'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_reconciliation_contains_no_sql_http_runtime_or_reconstruction(): void
    {
        $source = strtolower($this->source());

        foreach (['select ', 'update ', 'insert ', 'delete ', 'http', 'artisan', 'queue', 'scheduler', 'cron', 'searchlistingprojectionbuilder', 'seolistingprojectionbuilder', 'publiclistingreadmodelbuilder', 'while ('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    private function source(): string
    {
        $root = dirname(__DIR__, 2).'/app/Application/PublicProjectionReconciliation';
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

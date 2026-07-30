<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class PostgreSqlPublicProjectionStoreArchitectureTest extends TestCase
{
    public function test_store_depends_only_on_projection_contracts_read_models_and_pdo(): void
    {
        $source = $this->source();

        foreach (['Illuminate\\', 'Laravel\\', 'Appart\\Modules\\', 'Controller', 'PublicProjectionDeliveryWorker', 'PublicListingProjectionUpdater'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_store_contains_no_runtime_http_or_business_projection_builders(): void
    {
        $source = strtolower($this->source());

        foreach (['artisan', 'queue', 'scheduler', 'http', 'searchlistingprojectionbuilder', 'seolistingprojectionbuilder', 'canonicalpolicy', 'publiclistingreadmodelbuilder'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    private function source(): string
    {
        $root = dirname(__DIR__, 2).'/app/Infrastructure/PublicProjectionStore';
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

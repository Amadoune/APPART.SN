<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class PublicProjectionUpdaterIntegrationArchitectureTest extends TestCase
{
    public function test_consumer_is_purely_applicative(): void
    {
        $source = $this->source();

        foreach (['Illuminate\\', 'Laravel\\', 'PDO', 'App\\Infrastructure\\', 'Appart\\Modules\\', 'Repository', 'Controller'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_consumer_delegates_without_reconstruction_or_business_decisions(): void
    {
        $source = strtolower($this->source());

        foreach (['select ', 'update ', 'insert ', 'delete ', 'searchlistingprojectionbuilder', 'seolistingprojectionbuilder', 'publiclistingreadmodelbuilder', 'canonicalpolicy', 'json-ld', 'robots'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    private function source(): string
    {
        $root = dirname(__DIR__, 2).'/app/Application/PublicProjectionUpdaterIntegration';
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

<?php

namespace Tests\Architecture;

use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

final class PublicProjectionStoreContractArchitectureTest extends TestCase
{
    public function test_contract_types_are_framework_and_infrastructure_independent(): void
    {
        foreach ($this->productionFiles() as $path) {
            $contents = $this->contents($path);
            self::assertDoesNotMatchRegularExpression(
                '/(?:Illuminate\\\\|Laravel\\\\|PDO|PostgreSql|Appart\\\\Modules\\\\|Infrastructure\\\\Persistence|Repository)/',
                $contents,
                $path,
            );
        }
    }

    public function test_writer_exposes_only_specialized_technical_intents(): void
    {
        $methods = array_map(static fn ($method): string => $method->getName(), (new ReflectionClass(PublicListingProjectionWriter::class))->getMethods());

        self::assertSame(['applyCurrent', 'replaceCanonical', 'applyTombstone', 'writeCandidate'], $methods);
        self::assertNotContains('save', $methods);
        self::assertNotContains('put', $methods);
    }

    public function test_snapshot_and_watermark_are_specialized_and_immutable(): void
    {
        self::assertTrue((new ReflectionClass(PublicListingProjectionRecord::class))->isReadOnly());
        self::assertTrue((new ReflectionClass(PublicProjectionWatermark::class))->isReadOnly());

        $names = array_map(static fn (string $path): string => pathinfo($path, PATHINFO_FILENAME), $this->productionFiles());
        self::assertNotContains('Repository', $names);
        self::assertNotContains('Mapper', $names);
        self::assertNotContains('Snapshot', $names);
        self::assertNotContains('Version', $names);
        self::assertNotContains('Watermark', $names);
        self::assertNotContains('ProjectionStore', $names);
    }

    public function test_contract_contains_no_clock_based_causal_ordering(): void
    {
        foreach ($this->productionFiles() as $path) {
            $contents = $this->contents($path);
            self::assertDoesNotMatchRegularExpression('/(?:DateTime|decidedAt|updatedAt|createdAt|now\s*\(|microtime|time\s*\()/', $contents, $path);
        }
    }

    public function test_fake_exists_only_below_tests_and_no_production_binding_exists(): void
    {
        self::assertFileExists(dirname(__DIR__).DIRECTORY_SEPARATOR.'Unit'.DIRECTORY_SEPARATOR.'Contracts'.DIRECTORY_SEPARATOR.'PublicProjectionStore'.DIRECTORY_SEPARATOR.'Support'.DIRECTORY_SEPARATOR.'FakePublicListingProjectionStore.php');

        foreach ($this->productionFiles() as $path) {
            self::assertStringNotContainsString('FakePublicListingProjectionStore', $this->contents($path));
        }

        $provider = $this->contents(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Providers'.DIRECTORY_SEPARATOR.'AppServiceProvider.php');
        self::assertStringNotContainsString('PublicListingProjectionWriter', $provider);
        self::assertStringNotContainsString('PublicListingQuery', $provider);
    }

    public function test_public_listing_query_remains_current_only(): void
    {
        $query = $this->contents(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Application'.DIRECTORY_SEPARATOR.'Contract'.DIRECTORY_SEPARATOR.'PublicListingQuery.php');

        self::assertStringContainsString('findByCanonicalPath', $query);
        self::assertStringContainsString('current public canonical path', $query);
        self::assertStringContainsString('Historical paths', $query);
        self::assertStringNotContainsString('findByListingId', $query);
        self::assertStringNotContainsString('findHistorical', $query);
    }

    /** @return list<string> */
    private function productionFiles(): array
    {
        $root = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Application'.DIRECTORY_SEPARATOR.'PublicProjectionStore';
        $paths = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $paths[] = $file->getPathname();
            }
        }
        sort($paths);

        return $paths;
    }

    private function contents(string $path): string
    {
        $contents = file_get_contents($path);
        self::assertIsString($contents);

        return $contents;
    }
}

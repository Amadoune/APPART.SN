<?php

namespace Tests\Architecture;

use App\Application\PublicProjectionStore\PublicProjectionWriteResult;
use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdater;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;

final class PublicProjectionUpdaterArchitectureTest extends TestCase
{
    public function test_updater_is_final_readonly_and_exposes_only_orchestration(): void
    {
        $type = new ReflectionClass(PublicListingProjectionUpdater::class);
        self::assertTrue($type->isFinal());
        self::assertTrue($type->isReadOnly());
        self::assertSame(['__construct', 'update'], array_map(static fn ($method): string => $method->getName(), $type->getMethods(ReflectionMethod::IS_PUBLIC)));
    }

    public function test_updater_layer_has_no_framework_database_or_runtime_dependency(): void
    {
        foreach ($this->files() as $path) {
            $contents = file_get_contents($path);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/(?:Illuminate\\|Laravel\\|PDO|PostgreSql|Infrastructure\\|Repository|Controller|View|Route|DB::|\bSQL\b|ServiceProvider|Dispatcher|Queue|Outbox)/i', $contents, $path);
        }
    }

    public function test_updater_delegates_the_business_policy_without_implementing_it(): void
    {
        $contents = file_get_contents($this->root().DIRECTORY_SEPARATOR.'PublicListingProjectionUpdater.php');
        self::assertIsString($contents);
        self::assertStringContainsString('ListingSeoDecisionPolicy', $contents);
        self::assertStringContainsString('->decide(', $contents);
        self::assertDoesNotMatchRegularExpression('/(?:indexable|robots|jsonLd|redirect|str_slug|slugify|canonical\s*=)/i', $contents);
    }

    public function test_writer_result_match_is_exhaustive_without_default(): void
    {
        $contents = file_get_contents($this->root().DIRECTORY_SEPARATOR.'PublicListingProjectionUpdater.php');
        self::assertIsString($contents);
        foreach (PublicProjectionWriteResult::cases() as $case) {
            self::assertStringContainsString('PublicProjectionWriteResult::'.$case->name, $contents);
        }
        self::assertStringNotContainsString('default =>', $contents);
    }

    /** @return list<string> */
    private function files(): array
    {
        $files = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root())) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function root(): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Application'.DIRECTORY_SEPARATOR.'PublicProjectionUpdater';
    }
}

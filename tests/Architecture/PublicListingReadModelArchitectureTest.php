<?php

namespace Tests\Architecture;

use App\ReadModels\PublicListingReadModel;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

final class PublicListingReadModelArchitectureTest extends TestCase
{
    public function test_public_read_model_is_final_readonly_and_behaviorless(): void
    {
        $type = new ReflectionClass(PublicListingReadModel::class);

        self::assertTrue($type->isFinal());
        self::assertTrue($type->isReadOnly());
        self::assertSame(['__construct'], array_map(static fn ($method): string => $method->getName(), $type->getMethods()));
    }

    public function test_builder_depends_only_on_the_two_projections_and_local_read_model_types(): void
    {
        $contents = file_get_contents($this->builderPath());
        self::assertIsString($contents);
        preg_match_all('/^use\s+([^;]+);/m', $contents, $matches);

        self::assertSame([
            'App\\Projections\\SearchListingProjection',
            'App\\Projections\\SeoListingProjection',
        ], $matches[1]);
    }

    public function test_read_model_files_contain_no_framework_infrastructure_or_generation_dependency(): void
    {
        foreach ([$this->modelPath(), $this->builderPath(), $this->exceptionPath()] as $path) {
            $contents = file_get_contents($path);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression(
                '/(?:Illuminate\\\\|Laravel\\\\|Appart\\\\Modules\\\\|Repository|Registry|Catalog|PDO|\\bSQL\\b|Migration|Controller|Route|random_|uniqid|uuid|now\s*\()/i',
                $contents,
                $path.' contains a forbidden dependency.',
            );
        }
    }

    public function test_no_domain_depends_on_read_models_or_projections(): void
    {
        $modules = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Modules';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modules)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            self::assertStringNotContainsString('App\\ReadModels\\', $contents, $file->getPathname());
            self::assertStringNotContainsString('App\\Projections\\', $contents, $file->getPathname());
        }
    }

    private function modelPath(): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'ReadModels'.DIRECTORY_SEPARATOR.'PublicListingReadModel.php';
    }

    private function builderPath(): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'ReadModels'.DIRECTORY_SEPARATOR.'PublicListingReadModelBuilder.php';
    }

    private function exceptionPath(): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'ReadModels'.DIRECTORY_SEPARATOR.'InconsistentPublicListingProjection.php';
    }
}

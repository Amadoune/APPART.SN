<?php

namespace Tests\Architecture;

use App\Projections\SeoListingProjection;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

final class SeoListingProjectionArchitectureTest extends TestCase
{
    public function test_projection_is_a_final_readonly_non_domain_model(): void
    {
        $type = new ReflectionClass(SeoListingProjection::class);

        self::assertTrue($type->isFinal());
        self::assertTrue($type->isReadOnly());
        self::assertSame(['__construct'], array_map(static fn ($method): string => $method->getName(), $type->getMethods()));
    }

    public function test_seo_projection_files_have_no_infrastructure_framework_or_source_aggregate_dependency(): void
    {
        foreach ([$this->projectionPath(), $this->builderPath()] as $path) {
            $contents = file_get_contents($path);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression(
                '/(?:Illuminate\\\\|Laravel\\\\|PDO|\\bSQL\\b|Repository|Registry|Appart\\\\Modules\\\\(?:ListingLifecycle|RealEstateCatalog|Media)\\\\)/',
                $contents,
                $path.' contains a forbidden dependency.',
            );
        }
    }

    public function test_builder_cannot_instantiate_policy_or_generate_external_values(): void
    {
        $contents = file_get_contents($this->builderPath());
        self::assertIsString($contents);
        $tokens = token_get_all($contents);
        $createdTypes = [];
        foreach ($tokens as $index => $token) {
            if (! is_array($token) || $token[0] !== T_NEW) {
                continue;
            }
            for ($next = $index + 1; isset($tokens[$next]); $next++) {
                if (is_array($tokens[$next]) && $tokens[$next][0] === T_STRING) {
                    $createdTypes[] = $tokens[$next][1];
                    break;
                }
            }
        }

        self::assertSame(['SeoListingProjection', 'LogicException'], $createdTypes);
        self::assertDoesNotMatchRegularExpression('/(?:CanonicalPolicy|DateTimeImmutable|random_|uniqid|uuid|now\s*\()/i', $contents);
    }

    public function test_content_seo_domain_does_not_depend_on_app_projections(): void
    {
        $domain = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Modules'.DIRECTORY_SEPARATOR.'ContentSeo'.DIRECTORY_SEPARATOR.'Domain';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($domain)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            self::assertStringNotContainsString('App\\Projections\\', $contents, $file->getPathname());
        }
    }

    private function projectionPath(): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Projections'.DIRECTORY_SEPARATOR.'SeoListingProjection.php';
    }

    private function builderPath(): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Projections'.DIRECTORY_SEPARATOR.'SeoListingProjectionBuilder.php';
    }
}

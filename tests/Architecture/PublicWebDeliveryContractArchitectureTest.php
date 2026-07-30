<?php

namespace Tests\Architecture;

use App\Application\Contract\PublicListingQuery;
use App\ReadModels\PublicListingReadModel;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionNamedType;

final class PublicWebDeliveryContractArchitectureTest extends TestCase
{
    public function test_public_query_is_a_read_only_contract_returning_only_the_public_read_model(): void
    {
        $contract = new ReflectionClass(PublicListingQuery::class);
        $methods = $contract->getMethods();

        self::assertTrue($contract->isInterface());
        self::assertCount(1, $methods);
        self::assertSame('findByCanonicalPath', $methods[0]->getName());
        self::assertSame('string', (string) $methods[0]->getParameters()[0]->getType());
        self::assertInstanceOf(ReflectionNamedType::class, $methods[0]->getReturnType());
        self::assertSame(PublicListingReadModel::class, $methods[0]->getReturnType()->getName());
        self::assertTrue($methods[0]->getReturnType()->allowsNull());
    }

    public function test_public_query_contract_has_no_domain_framework_or_persistence_dependency(): void
    {
        $contents = file_get_contents($this->contractPath());
        self::assertIsString($contents);
        self::assertDoesNotMatchRegularExpression(
            '/(?:Appart\\\\Modules\\\\|Illuminate\\\\|Repository|Catalog|Registry|PDO|\\bSQL\\b|Migration|Controller|Route|View)/i',
            $contents,
        );
        self::assertStringNotContainsString('ListingId', $contents);
    }

    public function test_no_production_public_query_implementation_is_smuggled_into_application_contracts(): void
    {
        $application = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Application';
        self::assertFileExists($this->contractPath());

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($application)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/implements\s+PublicListingQuery/', $contents, $file->getPathname());
        }
    }

    private function contractPath(): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Application'.DIRECTORY_SEPARATOR.'Contract'.DIRECTORY_SEPARATOR.'PublicListingQuery.php';
    }
}

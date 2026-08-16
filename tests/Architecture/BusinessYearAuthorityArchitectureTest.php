<?php

namespace Tests\Architecture;

use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BusinessYear;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class BusinessYearAuthorityArchitectureTest extends TestCase
{
    public function test_authority_is_a_pure_real_estate_catalog_application_boundary(): void
    {
        $root = dirname(__DIR__, 2);
        $directory = $root.'/src/Modules/RealEstateCatalog/Application/BusinessYear';
        $files = array_merge(glob($directory.'/*.php') ?: [], glob($directory.'/Contract/*.php') ?: []);
        $source = implode("\n", array_map(static fn (string $file): string => (string) file_get_contents($file), $files));

        self::assertStringContainsString('namespace Appart\\Modules\\RealEstateCatalog\\Application\\BusinessYear', $source);
        self::assertStringContainsString("new DateTimeZone('UTC')", $source);
        foreach (['PDO', 'SQL', 'Migration', 'Repository', 'Ledger', 'Clock', 'random', 'PropertyAuthoring', 'Geography', 'Projection', 'Search', 'Illuminate', 'Http', 'now(', 'date('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_register_and_update_accept_the_existing_business_year_value_object(): void
    {
        foreach (['RegisterProperty', 'UpdateProperty'] as $useCase) {
            $method = new ReflectionMethod('Appart\\Modules\\RealEstateCatalog\\Application\\UseCase\\'.$useCase, 'execute');
            $parameter = array_values(array_filter($method->getParameters(), static fn ($candidate): bool => $candidate->getName() === 'businessYear'))[0];
            self::assertSame(BusinessYear::class, (string) $parameter->getType());
        }
    }

    public function test_property_type_policy_remains_outside_the_authority(): void
    {
        $policy = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Domain/Policy/PropertyTypePolicy.php');

        self::assertStringNotContainsString('BusinessYearAuthority', $policy);
        self::assertStringContainsString('futureConstructionYear', $policy);
    }
}

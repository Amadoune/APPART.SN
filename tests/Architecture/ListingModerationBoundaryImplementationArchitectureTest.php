<?php

namespace Tests\Architecture;

use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\OwnerListingModerationCommandGatewayV1;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ListingModerationBoundaryImplementationArchitectureTest extends TestCase
{
    #[Test]
    public function owner_gateway_has_exactly_the_four_authorized_dependencies(): void
    {
        $class = new ReflectionClass(
            OwnerListingModerationCommandGatewayV1::class,
        );
        $constructor = $class->getConstructor();
        self::assertNotNull($constructor);
        self::assertSame([
            'ListingModerationIntentStore',
            'ListingModerationIntentTransaction',
            'ListingPublicationOrchestrator',
            'ListingPublicationWorkflowStore',
        ], array_map(
            static fn (\ReflectionParameter $parameter): string => (new ReflectionClass(
                (string) $parameter->getType(),
            ))->getShortName(),
            $constructor->getParameters(),
        ));
    }

    #[Test]
    public function implementations_are_owner_application_code_without_forbidden_dependencies(): void
    {
        foreach ([
            'OwnerListingModerationReaderV1.php',
            'OwnerListingModerationCommandGatewayV1.php',
        ] as $file) {
            $source = (string) file_get_contents(
                dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Application/ModerationBoundary/'.$file,
            );
            foreach ([
                'PDO',
                'PostgreSql',
                'Illuminate',
                'Infrastructure',
                'ModerationReports',
                'IdentityAccess',
                'RuntimeHealth',
            ] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source);
            }
        }
    }

    #[Test]
    public function runtime_bindings_are_singleton_lazy_and_unique(): void
    {
        $provider = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php',
        );

        foreach ([
            'OwnerListingModerationReaderV1::class, ListingModerationReaderV1::class',
            'OwnerListingModerationCommandGatewayV1::class, ListingModerationCommandGatewayV1::class',
        ] as $alias) {
            self::assertSame(1, substr_count($provider, 'alias('.$alias.')'));
        }
        self::assertSame(1, substr_count($provider, 'singleton(OwnerListingModerationReaderV1::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(OwnerListingModerationCommandGatewayV1::class)'));
    }
}

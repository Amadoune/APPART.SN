<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProfessionalOwnerReadRuntimeBindingsArchitectureTest extends TestCase
{
    #[Test]
    public function bindings_are_unique_owner_scoped_and_add_no_provider_or_http_surface(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        $routes = (string) file_get_contents($root.'/routes/web.php');

        self::assertSame(1, substr_count($provider, 'OwnerProfessionalMandateResolverV1::class, ProfessionalMandateResolverV1::class'));
        self::assertSame(1, substr_count($provider, 'OwnerProfessionalPublicStatusReaderV1::class, ProfessionalPublicStatusReaderV1::class'));
        self::assertSame(1, substr_count($provider, 'PostgreSqlProfessionalMandateOwnerSource::class, ProfessionalMandateOwnerSource::class'));
        self::assertStringNotContainsString('ProfessionalOwnerRead', $providers);
        self::assertStringNotContainsString('professional-mandate-resolution', $routes);
        self::assertStringNotContainsString('professional-public-status', $routes);
    }

    #[Test]
    public function implementations_expose_no_http_event_delivery_outbox_or_aggregate_dependency(): void
    {
        $root = dirname(__DIR__, 2);
        foreach ([
            $root.'/src/Modules/Professionals/Application/ProfessionalMandateResolution/OwnerProfessionalMandateResolverV1.php',
            $root.'/src/Modules/Professionals/Infrastructure/Runtime/OwnerProfessionalPublicStatusReaderV1.php',
        ] as $file) {
            $source = (string) file_get_contents($file);
            foreach (['Controller', 'Route', 'Middleware', 'Event', 'Delivery', 'Outbox', 'ProfessionalRegistry', 'RepresentativeMandate', 'RepresentativeId', 'SELECT '] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source);
            }
        }
    }
}

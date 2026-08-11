<?php

namespace Tests\Feature;

use App\Application\ListingPublicationCommandGateway\DeterministicListingPublicationCommandGateway;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\Contract\ListingPublicationCommandGatewayV1;
use Tests\TestCase;

final class ListingPublicationCommandGatewayCompositionTest extends TestCase
{
    public function test_gateway_alias_resolves_to_the_singleton_composition(): void
    {
        $gateway = $this->app->make(ListingPublicationCommandGatewayV1::class);

        self::assertInstanceOf(DeterministicListingPublicationCommandGateway::class, $gateway);
        self::assertSame($gateway, $this->app->make(ListingPublicationCommandGatewayV1::class));
    }
}

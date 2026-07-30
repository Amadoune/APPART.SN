<?php

namespace Tests\Feature;

use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\Contract\ListingModerationCommandGatewayV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\Contract\ListingModerationReaderV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\OwnerListingModerationCommandGatewayV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\OwnerListingModerationReaderV1;
use PDO;
use Tests\TestCase;

final class ListingModerationBoundaryRuntimeTest extends TestCase
{
    public function test_container_resolves_unique_singleton_owner_implementations(): void
    {
        $this->app->instance(PDO::class, new PDO('sqlite::memory:'));

        $reader = $this->app->make(ListingModerationReaderV1::class);
        $gateway = $this->app->make(ListingModerationCommandGatewayV1::class);

        self::assertInstanceOf(OwnerListingModerationReaderV1::class, $reader);
        self::assertInstanceOf(OwnerListingModerationCommandGatewayV1::class, $gateway);
        self::assertSame($reader, $this->app->make(ListingModerationReaderV1::class));
        self::assertSame($gateway, $this->app->make(ListingModerationCommandGatewayV1::class));
    }
}

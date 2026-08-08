<?php

namespace Tests\Feature;

use Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary\Contract\ListingContactabilityReaderV1;
use Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary\OwnerListingContactabilityReaderV1;
use PDO;
use Tests\TestCase;

final class ListingContactabilityBoundaryRuntimeTest extends TestCase
{
    public function test_container_resolves_unique_singleton_owner_reader(): void
    {
        $this->app->instance(PDO::class, new PDO('sqlite::memory:'));

        $reader = $this->app->make(ListingContactabilityReaderV1::class);

        self::assertInstanceOf(OwnerListingContactabilityReaderV1::class, $reader);
        self::assertSame($reader, $this->app->make(ListingContactabilityReaderV1::class));
    }
}

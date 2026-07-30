<?php

namespace Tests\Feature;

use App\Application\PropertyListingAuthoringHttp\Contract\PropertyListingAuthoringHttpRuntime;
use App\Application\PropertyListingAuthoringOperations\Contract\PropertyListingAuthoringOperations;
use App\Application\PropertyListingAuthoringRuntime\Contract\PropertyListingAuthoringRuntimeV1;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use Appart\Modules\ListingLifecycle\Application\Creation\Contract\CreateListingDraftV1;
use Appart\Modules\ListingLifecycle\Application\Creation\Contract\ListingCreationTransaction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationOrchestrator;
use Closure;
use Tests\TestCase;

final class PropertyListingAuthoringOperationsRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PropertyListingAuthoringRuntimeV1::class, $this->createMock(PropertyListingAuthoringRuntimeV1::class));
        $this->app->instance(CreateListingDraftV1::class, $this->createMock(CreateListingDraftV1::class));
        $this->app->instance(ListingPublicationOrchestrator::class, $this->createMock(ListingPublicationOrchestrator::class));
        $this->app->instance(ListingCreationTransaction::class, new class implements ListingCreationTransaction
        {
            public function run(Closure $operation): mixed
            {
                return $operation();
            }
        });
    }

    public function test_operations_are_a_lazy_unique_runtime_binding(): void
    {
        self::assertFalse($this->app->resolved(PropertyListingAuthoringOperations::class));

        $operations = $this->app->make(PropertyListingAuthoringOperations::class);

        self::assertSame($operations, $this->app->make(PropertyListingAuthoringOperations::class));
    }

    public function test_operations_do_not_replace_frozen_http_or_runtime_health(): void
    {
        $http = $this->app->make(PropertyListingAuthoringHttpRuntime::class);
        $operations = $this->app->make(PropertyListingAuthoringOperations::class);

        self::assertNotSame($http, $operations);
        self::assertCount(60, PublicProjectionRuntimeRequirements::certified());
    }
}

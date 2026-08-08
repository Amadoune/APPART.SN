<?php

namespace Tests\Feature;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\Contract\SearchQueryResolutionOwnerSourceRuntimeV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\SearchQueryResolutionOwnerSourceRuntimeAvailability;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\SearchQueryResolutionOwnerSourceRuntimeDiagnostics;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead\Contract\SearchQueryResolutionOwnerSourceRuntimeReadV1;
use Tests\TestCase;

final class SearchQueryResolutionOwnerSourceRuntimeReadCompositionTest extends TestCase
{
    public function test_binding_is_lazy_unique_and_singleton(): void
    {
        self::assertFalse($this->app->resolved(SearchQueryResolutionOwnerSourceRuntimeReadV1::class));
        self::assertTrue($this->app->bound(SearchQueryResolutionOwnerSourceRuntimeReadV1::class));
        self::assertTrue($this->app->bound(SearchQueryResolutionOwnerSourceRuntimeV1::class));
        $this->app->instance(SearchQueryResolutionOwnerSourceRuntimeV1::class, new FeatureQueryResolutionRuntimeStub);
        $runtimeRead = $this->app->make(SearchQueryResolutionOwnerSourceRuntimeReadV1::class);
        self::assertSame($runtimeRead, $this->app->make(SearchQueryResolutionOwnerSourceRuntimeReadV1::class));
    }
}

final readonly class FeatureQueryResolutionRuntimeStub implements SearchQueryResolutionOwnerSourceRuntimeV1
{
    public function availability(): SearchQueryResolutionOwnerSourceRuntimeAvailability
    {
        return SearchQueryResolutionOwnerSourceRuntimeAvailability::Available;
    }

    public function diagnostics(): SearchQueryResolutionOwnerSourceRuntimeDiagnostics
    {
        return new SearchQueryResolutionOwnerSourceRuntimeDiagnostics('stub', 'stub-v1', $this->availability());
    }
}

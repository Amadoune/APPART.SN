<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\Contract\SearchQueryResolutionOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\Contract\SearchQueryResolutionOwnerSourceRuntimeV1;

final readonly class DeterministicSearchQueryResolutionOwnerSourceRuntimeV1 implements SearchQueryResolutionOwnerSourceRuntimeV1
{
    private const RUNTIME_ID = 'search-query-resolution-owner-source-runtime';

    private const VERSION = 'search-query-resolution-owner-source-runtime-v1';

    public function __construct(private SearchQueryResolutionOwnerSourceRuntimeAvailabilityPolicy $availabilityPolicy) {}

    public function availability(): SearchQueryResolutionOwnerSourceRuntimeAvailability
    {
        return $this->availabilityPolicy->inspect();
    }

    public function diagnostics(): SearchQueryResolutionOwnerSourceRuntimeDiagnostics
    {
        return new SearchQueryResolutionOwnerSourceRuntimeDiagnostics(self::RUNTIME_ID, self::VERSION, $this->availability());
    }
}

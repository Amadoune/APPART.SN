<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime;

use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime\Contract\SearchOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime\Contract\SearchOwnerSourceRuntimeV1;

final readonly class DeterministicSearchOwnerSourceRuntimeV1 implements SearchOwnerSourceRuntimeV1
{
    private const RUNTIME_ID = 'search-discovery.owner-source';

    private const VERSION = 'search-owner-source-runtime-v1';

    public function __construct(private SearchOwnerSourceRuntimeAvailabilityPolicy $availabilityPolicy) {}

    public function availability(): SearchOwnerSourceRuntimeAvailability
    {
        return $this->availabilityPolicy->inspect();
    }

    public function diagnostics(): SearchOwnerSourceRuntimeDiagnostics
    {
        return new SearchOwnerSourceRuntimeDiagnostics(self::RUNTIME_ID, self::VERSION, $this->availability());
    }
}

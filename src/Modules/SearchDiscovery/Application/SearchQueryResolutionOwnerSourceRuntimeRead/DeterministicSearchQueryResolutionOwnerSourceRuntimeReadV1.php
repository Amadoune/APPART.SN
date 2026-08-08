<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\Contract\SearchQueryResolutionOwnerSourceRuntimeV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead\Contract\SearchQueryResolutionOwnerSourceRuntimeReadPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead\Contract\SearchQueryResolutionOwnerSourceRuntimeReadV1;
use Throwable;

final readonly class DeterministicSearchQueryResolutionOwnerSourceRuntimeReadV1 implements SearchQueryResolutionOwnerSourceRuntimeReadV1
{
    private const RUNTIME_READ_ID = 'search-query-resolution-runtime-read';

    private const VERSION = 'search-query-resolution-runtime-read-v1';

    public function __construct(
        private SearchQueryResolutionOwnerSourceRuntimeV1 $runtime,
        private SearchQueryResolutionOwnerSourceRuntimeReadPolicy $policy,
    ) {}

    public function read(): SearchQueryResolutionOwnerSourceRuntimeReadResult
    {
        try {
            return $this->policy->reduce($this->runtime->availability());
        } catch (Throwable) {
            return SearchQueryResolutionOwnerSourceRuntimeReadResult::dependencyUnavailable();
        }
    }

    public function diagnostics(): SearchQueryResolutionOwnerSourceRuntimeReadDiagnostics
    {
        $result = $this->read();
        $availability = match ($result->status) {
            SearchQueryResolutionOwnerSourceRuntimeReadStatus::Found => SearchQueryResolutionOwnerSourceRuntimeReadAvailability::Available,
            SearchQueryResolutionOwnerSourceRuntimeReadStatus::Corrupted => SearchQueryResolutionOwnerSourceRuntimeReadAvailability::Corrupted,
            SearchQueryResolutionOwnerSourceRuntimeReadStatus::DependencyUnavailable => SearchQueryResolutionOwnerSourceRuntimeReadAvailability::DependencyUnavailable,
        };

        return new SearchQueryResolutionOwnerSourceRuntimeReadDiagnostics(self::RUNTIME_READ_ID, self::VERSION, $availability);
    }
}

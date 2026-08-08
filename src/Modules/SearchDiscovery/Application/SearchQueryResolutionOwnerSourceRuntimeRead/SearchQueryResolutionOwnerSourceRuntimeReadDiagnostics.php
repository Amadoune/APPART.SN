<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead;

final readonly class SearchQueryResolutionOwnerSourceRuntimeReadDiagnostics
{
    public function __construct(
        public string $runtimeId,
        public string $version,
        public SearchQueryResolutionOwnerSourceRuntimeReadAvailability $availability,
    ) {}
}

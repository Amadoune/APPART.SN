<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime;

final readonly class SearchQueryResolutionOwnerSourceRuntimeDiagnostics
{
    public function __construct(
        public string $runtimeId,
        public string $version,
        public SearchQueryResolutionOwnerSourceRuntimeAvailability $availability,
    ) {}
}

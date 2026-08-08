<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead;

final readonly class SearchOwnerSourceRuntimeReadDiagnostics
{
    public function __construct(
        public string $runtimeReadId,
        public string $version,
        public SearchOwnerSourceRuntimeReadAvailability $availability,
    ) {}
}

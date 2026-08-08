<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime;

final readonly class SearchOwnerSourceRuntimeDiagnostics
{
    public function __construct(
        public string $runtimeId,
        public string $version,
        public SearchOwnerSourceRuntimeAvailability $availability,
    ) {}
}

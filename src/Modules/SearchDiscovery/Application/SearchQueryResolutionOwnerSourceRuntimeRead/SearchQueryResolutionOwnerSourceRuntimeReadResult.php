<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead;

final readonly class SearchQueryResolutionOwnerSourceRuntimeReadResult
{
    private function __construct(public SearchQueryResolutionOwnerSourceRuntimeReadStatus $status) {}

    public static function found(): self
    {
        return new self(SearchQueryResolutionOwnerSourceRuntimeReadStatus::Found);
    }

    public static function corrupted(): self
    {
        return new self(SearchQueryResolutionOwnerSourceRuntimeReadStatus::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(SearchQueryResolutionOwnerSourceRuntimeReadStatus::DependencyUnavailable);
    }
}

<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead;

final readonly class SearchOwnerSourceRuntimeReadResult
{
    private function __construct(public SearchOwnerSourceRuntimeReadStatus $status) {}

    public static function allowed(): self
    {
        return new self(SearchOwnerSourceRuntimeReadStatus::Allowed);
    }

    public static function empty(): self
    {
        return new self(SearchOwnerSourceRuntimeReadStatus::Empty);
    }

    public static function corrupted(): self
    {
        return new self(SearchOwnerSourceRuntimeReadStatus::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(SearchOwnerSourceRuntimeReadStatus::DependencyUnavailable);
    }
}

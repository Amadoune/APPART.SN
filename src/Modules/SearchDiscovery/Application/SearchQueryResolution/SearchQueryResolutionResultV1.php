<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolution;

final readonly class SearchQueryResolutionResultV1
{
    private function __construct(public SearchQueryResolutionStatusV1 $status) {}

    public static function found(): self
    {
        return new self(SearchQueryResolutionStatusV1::Found);
    }

    public static function empty(): self
    {
        return new self(SearchQueryResolutionStatusV1::Empty);
    }

    public static function corrupted(): self
    {
        return new self(SearchQueryResolutionStatusV1::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(SearchQueryResolutionStatusV1::DependencyUnavailable);
    }
}

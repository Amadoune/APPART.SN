<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead;

final readonly class SearchQueryResultV1
{
    private function __construct(public SearchQueryResultStatusV1 $status) {}

    public static function found(): self
    {
        return new self(SearchQueryResultStatusV1::Found);
    }

    public static function empty(): self
    {
        return new self(SearchQueryResultStatusV1::Empty);
    }

    public static function corrupted(): self
    {
        return new self(SearchQueryResultStatusV1::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(SearchQueryResultStatusV1::DependencyUnavailable);
    }
}

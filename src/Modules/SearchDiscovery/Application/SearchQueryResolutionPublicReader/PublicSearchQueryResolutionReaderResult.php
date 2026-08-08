<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionPublicReader;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionResultV1;

final readonly class PublicSearchQueryResolutionReaderResult
{
    public function __construct(public PublicSearchQueryResolutionReaderStatus $status) {}

    public function toPublicResultV1(): SearchQueryResolutionResultV1
    {
        return match ($this->status) {
            PublicSearchQueryResolutionReaderStatus::Found => SearchQueryResolutionResultV1::found(),
            PublicSearchQueryResolutionReaderStatus::Empty => SearchQueryResolutionResultV1::empty(),
            PublicSearchQueryResolutionReaderStatus::Corrupted => SearchQueryResolutionResultV1::corrupted(),
            PublicSearchQueryResolutionReaderStatus::DependencyUnavailable => SearchQueryResolutionResultV1::dependencyUnavailable(),
        };
    }
}

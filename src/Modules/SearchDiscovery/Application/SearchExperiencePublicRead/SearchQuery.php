<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead;

final readonly class SearchQuery
{
    public function __construct(public string $value) {}

    public function canonical(): string
    {
        return $this->value;
    }
}

<?php

namespace Appart\Modules\SearchDiscovery\Application\Materialization;

final readonly class PublicSearchMaterializationSourceResult
{
    private function __construct(
        public PublicSearchMaterializationSourceStatus $status,
        public ?PublicSearchMaterializationSources $sources = null,
    ) {}

    public static function found(PublicSearchMaterializationSources $sources): self
    {
        return new self(PublicSearchMaterializationSourceStatus::Found, $sources);
    }

    public static function missing(): self
    {
        return new self(PublicSearchMaterializationSourceStatus::Missing);
    }

    public static function corrupted(): self
    {
        return new self(PublicSearchMaterializationSourceStatus::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(PublicSearchMaterializationSourceStatus::DependencyUnavailable);
    }
}

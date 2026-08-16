<?php

namespace Appart\Modules\ContentSeo\Application\Materialization;

final readonly class ContentSeoMaterializationSourceResult
{
    private function __construct(public ContentSeoMaterializationSourceStatus $status, public ?ContentSeoMaterializationSources $sources = null) {}

    public static function found(ContentSeoMaterializationSources $sources): self
    {
        return new self(ContentSeoMaterializationSourceStatus::Found, $sources);
    }

    public static function missing(): self
    {
        return new self(ContentSeoMaterializationSourceStatus::Missing);
    }

    public static function notReady(): self
    {
        return new self(ContentSeoMaterializationSourceStatus::NotReady);
    }

    public static function corrupted(): self
    {
        return new self(ContentSeoMaterializationSourceStatus::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(ContentSeoMaterializationSourceStatus::DependencyUnavailable);
    }
}

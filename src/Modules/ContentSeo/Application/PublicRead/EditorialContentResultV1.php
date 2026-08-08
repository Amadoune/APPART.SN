<?php

namespace Appart\Modules\ContentSeo\Application\PublicRead;

final readonly class EditorialContentResultV1
{
    private function __construct(public EditorialContentStatusV1 $status) {}

    public static function published(): self
    {
        return new self(EditorialContentStatusV1::Published);
    }

    public static function unpublished(): self
    {
        return new self(EditorialContentStatusV1::Unpublished);
    }

    public static function missing(): self
    {
        return new self(EditorialContentStatusV1::Missing);
    }

    public static function corrupted(): self
    {
        return new self(EditorialContentStatusV1::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(EditorialContentStatusV1::DependencyUnavailable);
    }
}

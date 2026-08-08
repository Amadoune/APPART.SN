<?php

namespace Appart\Modules\ContentSeo\Application\OwnerSource;

use Appart\Modules\ContentSeo\Application\PublicRead\EditorialContentStatusV1;

final readonly class EditorialContentReadResult
{
    private function __construct(public EditorialContentStatusV1 $status, public ?EditorialContentRevisionState $revision) {}

    public static function found(EditorialContentRevisionState $revision): self
    {
        return new self($revision->decision, $revision);
    }

    public static function missing(): self
    {
        return new self(EditorialContentStatusV1::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(EditorialContentStatusV1::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(EditorialContentStatusV1::DependencyUnavailable, null);
    }
}

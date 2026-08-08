<?php

namespace Appart\Modules\ContentSeo\Application\OwnerSource;

use Appart\Modules\ContentSeo\Application\PublicRead\OperationalSeoStatusV1;

final readonly class OperationalSeoReadResult
{
    private function __construct(public OperationalSeoStatusV1 $status, public ?OperationalSeoRevisionState $revision) {}

    public static function found(OperationalSeoRevisionState $revision): self
    {
        return new self($revision->decision, $revision);
    }

    public static function missing(): self
    {
        return new self(OperationalSeoStatusV1::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(OperationalSeoStatusV1::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(OperationalSeoStatusV1::DependencyUnavailable, null);
    }
}

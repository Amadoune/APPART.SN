<?php

namespace Appart\Modules\SecurityCompliance\Application\OwnerSource;

use Appart\Modules\SecurityCompliance\Application\PublicRead\IncidentStatusV1;

final readonly class IncidentReadResult
{
    private function __construct(public IncidentStatusV1 $status, public ?IncidentRevisionState $revision) {}

    public static function found(IncidentRevisionState $revision): self
    {
        return new self($revision->decision, $revision);
    }

    public static function missing(): self
    {
        return new self(IncidentStatusV1::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(IncidentStatusV1::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(IncidentStatusV1::DependencyUnavailable, null);
    }
}

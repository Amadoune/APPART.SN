<?php

namespace Appart\Modules\ExperienceAcceptance\Application\OwnerSource;

final readonly class ExperienceAcceptanceReadResult
{
    private function __construct(public ExperienceAcceptanceReadStatus $status, public ?ExperienceAcceptanceRevisionState $revision) {}

    public static function found(ExperienceAcceptanceRevisionState $revision): self
    {
        return new self(ExperienceAcceptanceReadStatus::Found, $revision);
    }

    public static function missing(): self
    {
        return new self(ExperienceAcceptanceReadStatus::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(ExperienceAcceptanceReadStatus::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(ExperienceAcceptanceReadStatus::DependencyUnavailable, null);
    }
}

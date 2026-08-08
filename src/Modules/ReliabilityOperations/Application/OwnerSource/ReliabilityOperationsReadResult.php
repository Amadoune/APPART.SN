<?php

namespace Appart\Modules\ReliabilityOperations\Application\OwnerSource;

final readonly class ReliabilityOperationsReadResult
{
    private function __construct(public ReliabilityOperationsReadStatus $status, public ?ReliabilityOperationsRevisionState $revision) {}

    public static function found(ReliabilityOperationsRevisionState $revision): self
    {
        return new self(ReliabilityOperationsReadStatus::Found, $revision);
    }

    public static function missing(): self
    {
        return new self(ReliabilityOperationsReadStatus::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(ReliabilityOperationsReadStatus::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(ReliabilityOperationsReadStatus::DependencyUnavailable, null);
    }
}

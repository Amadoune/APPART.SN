<?php

namespace Appart\Modules\SecurityCompliance\Application\OwnerSource;

use Appart\Modules\SecurityCompliance\Application\PublicRead\ComplianceControlStatusV1;

final readonly class ComplianceControlReadResult
{
    private function __construct(public ComplianceControlStatusV1 $status, public ?ComplianceControlRevisionState $revision) {}

    public static function found(ComplianceControlRevisionState $revision): self
    {
        return new self($revision->decision, $revision);
    }

    public static function missing(): self
    {
        return new self(ComplianceControlStatusV1::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(ComplianceControlStatusV1::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(ComplianceControlStatusV1::DependencyUnavailable, null);
    }
}

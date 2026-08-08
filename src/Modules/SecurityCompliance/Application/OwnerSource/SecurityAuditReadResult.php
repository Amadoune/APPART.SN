<?php

namespace Appart\Modules\SecurityCompliance\Application\OwnerSource;

use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityAuditStatusV1;

final readonly class SecurityAuditReadResult
{
    private function __construct(public SecurityAuditStatusV1 $status, public ?SecurityAuditRevisionState $revision) {}

    public static function found(SecurityAuditRevisionState $revision): self
    {
        return new self($revision->decision, $revision);
    }

    public static function missing(): self
    {
        return new self(SecurityAuditStatusV1::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(SecurityAuditStatusV1::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(SecurityAuditStatusV1::DependencyUnavailable, null);
    }
}

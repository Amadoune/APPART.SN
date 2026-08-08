<?php

namespace Appart\Modules\AdministrationConsole\Application\OwnerSource;

use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationAuditStatusV1;

final readonly class AdministrationAuditReadResult
{
    private function __construct(public AdministrationAuditStatusV1 $status, public ?AdministrationAuditRevisionState $revision) {}

    public static function found(AdministrationAuditRevisionState $revision): self
    {
        return new self($revision->decision, $revision);
    }

    public static function missing(): self
    {
        return new self(AdministrationAuditStatusV1::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(AdministrationAuditStatusV1::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(AdministrationAuditStatusV1::DependencyUnavailable, null);
    }
}

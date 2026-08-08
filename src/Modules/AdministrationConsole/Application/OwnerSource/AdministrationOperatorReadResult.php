<?php

namespace Appart\Modules\AdministrationConsole\Application\OwnerSource;

use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationOperatorStatusV1;

final readonly class AdministrationOperatorReadResult
{
    private function __construct(public AdministrationOperatorStatusV1 $status, public ?AdministrationOperatorRevisionState $revision) {}

    public static function found(AdministrationOperatorRevisionState $revision): self
    {
        return new self($revision->decision, $revision);
    }

    public static function missing(): self
    {
        return new self(AdministrationOperatorStatusV1::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(AdministrationOperatorStatusV1::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(AdministrationOperatorStatusV1::DependencyUnavailable, null);
    }
}

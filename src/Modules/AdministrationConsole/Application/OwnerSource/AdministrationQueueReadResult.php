<?php

namespace Appart\Modules\AdministrationConsole\Application\OwnerSource;

use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationQueueStatusV1;

final readonly class AdministrationQueueReadResult
{
    private function __construct(public AdministrationQueueStatusV1 $status, public ?AdministrationQueueRevisionState $revision) {}

    public static function found(AdministrationQueueRevisionState $revision): self
    {
        return new self($revision->decision, $revision);
    }

    public static function missing(): self
    {
        return new self(AdministrationQueueStatusV1::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(AdministrationQueueStatusV1::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(AdministrationQueueStatusV1::DependencyUnavailable, null);
    }
}

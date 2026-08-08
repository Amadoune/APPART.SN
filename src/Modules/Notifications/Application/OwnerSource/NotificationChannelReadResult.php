<?php

namespace Appart\Modules\Notifications\Application\OwnerSource;

use Appart\Modules\Notifications\Application\PublicRead\NotificationChannelStatusV1;

final readonly class NotificationChannelReadResult
{
    private function __construct(public NotificationChannelStatusV1 $status, public ?NotificationChannelRevisionState $revision) {}

    public static function found(NotificationChannelRevisionState $r): self
    {
        return new self($r->decision, $r);
    }

    public static function missing(): self
    {
        return new self(NotificationChannelStatusV1::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(NotificationChannelStatusV1::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(NotificationChannelStatusV1::DependencyUnavailable, null);
    }
}

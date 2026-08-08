<?php

namespace Appart\Modules\Notifications\Application\OwnerSource;

use Appart\Modules\Notifications\Application\PublicRead\NotificationPreferenceStatusV1;

final readonly class NotificationPreferenceReadResult
{
    private function __construct(public NotificationPreferenceStatusV1 $status, public ?NotificationPreferenceRevisionState $revision) {}

    public static function found(NotificationPreferenceRevisionState $r): self
    {
        return new self($r->decision, $r);
    }

    public static function missing(): self
    {
        return new self(NotificationPreferenceStatusV1::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(NotificationPreferenceStatusV1::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(NotificationPreferenceStatusV1::DependencyUnavailable, null);
    }
}

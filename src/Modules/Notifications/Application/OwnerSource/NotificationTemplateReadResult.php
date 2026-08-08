<?php

namespace Appart\Modules\Notifications\Application\OwnerSource;

use Appart\Modules\Notifications\Application\PublicRead\NotificationTemplateStatusV1;

final readonly class NotificationTemplateReadResult
{
    private function __construct(public NotificationTemplateStatusV1 $status, public ?NotificationTemplateRevisionState $revision) {}

    public static function found(NotificationTemplateRevisionState $r): self
    {
        return new self($r->decision, $r);
    }

    public static function missing(): self
    {
        return new self(NotificationTemplateStatusV1::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(NotificationTemplateStatusV1::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(NotificationTemplateStatusV1::DependencyUnavailable, null);
    }
}

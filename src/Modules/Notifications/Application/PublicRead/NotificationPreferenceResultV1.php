<?php

namespace Appart\Modules\Notifications\Application\PublicRead;

final readonly class NotificationPreferenceResultV1
{
    public function __construct(public NotificationPreferenceStatusV1 $status) {}
}

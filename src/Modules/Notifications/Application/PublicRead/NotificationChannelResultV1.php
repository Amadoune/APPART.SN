<?php

namespace Appart\Modules\Notifications\Application\PublicRead;

final readonly class NotificationChannelResultV1
{
    public function __construct(public NotificationChannelStatusV1 $status) {}
}
